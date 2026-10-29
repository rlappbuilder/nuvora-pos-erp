<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Unit;
use App\Models\MasterData\Warehouse;
use App\Models\Product\ProductVariant;

use App\Models\Inventory\StockTransferHeader;
use App\Models\Inventory\StockTransferReturnHeader;
use App\Models\Inventory\ProductStock;

use App\Services\Inventory\InventoryService;
use App\Services\Core\CodeGeneratorService;

use App\Http\Requests\Inventory\StockTransfer\StoreStockTransferRequest;
use App\Http\Requests\Inventory\StockTransfer\UpdateStockTransferRequest;
use App\Http\Requests\Inventory\StockTransfer\CancelStockTransferRequest;

class StockTransferController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index - Stock Transfer Dashboard Kit
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $activeBranchId = session('current_branch_id');

        /*
        |--------------------------------------------------------------------------
        | Stock Transfer Query
        |--------------------------------------------------------------------------
        */

        $query = StockTransferHeader::query()
            ->with([
                'fromBranch',
                'fromWarehouse',
                'toBranch',
                'toWarehouse',
                'creator',
                'details.variant.product',
                'details.unit',
            ])
            ->withCount('details')
            ->withSum('details', 'total_cost');

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('fromBranch', fn ($branch) =>
                        $branch->where('name', 'like', "%{$search}%")
                    )
                    ->orWhereHas('toBranch', fn ($branch) =>
                        $branch->where('name', 'like', "%{$search}%")
                    )
                    ->orWhereHas('fromWarehouse', fn ($warehouse) =>
                        $warehouse->where('name', 'like', "%{$search}%")
                    )
                    ->orWhereHas('toWarehouse', fn ($warehouse) =>
                        $warehouse->where('name', 'like', "%{$search}%")
                    );
            });
        }

        if ($request->filled('branch_id')) {
            $branchId = $request->input('branch_id');

            $query->where(function ($q) use ($branchId) {
                $q->where('from_branch_id', $branchId)
                    ->orWhere('to_branch_id', $branchId);
            });
        }

        if ($request->filled('warehouse_id')) {
            $warehouseId = $request->input('warehouse_id');

            $query->where(function ($q) use ($warehouseId) {
                $q->where('from_warehouse_id', $warehouseId)
                    ->orWhere('to_warehouse_id', $warehouseId);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'transaction_date',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'transaction_date',
                '<=',
                $request->input('date_to')
            );
        }

        $allowedSorts = [
            'id',
            'number',
            'transaction_date',
            'status',
            'total_cost',
        ];

        $sortBy = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        if ($sortBy === 'total_cost') {
            $query->orderBy('details_sum_total_cost', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        $statisticsQuery = clone $query;

        $statistics = [
            'total' => (clone $statisticsQuery)->count(),

            'draft' => (clone $statisticsQuery)
                ->where('status', 'Draft')
                ->count(),

            'rejected' => (clone $statisticsQuery)
                ->where('status', 'Rejected')
                ->count(),

            'posted' => (clone $statisticsQuery)
                ->where('status', 'Posted')
                ->count(),
        ];

        $perPage = $request->integer('per_page', 10);

        $perPage = in_array($perPage, [10, 25, 50, 100], true)
            ? $perPage
            : 10;

        $transfers = $query
            ->paginate($perPage)
            ->withQueryString();

        $transfers->getCollection()->transform(function ($transfer) {
            $transfer->details_count = $transfer->details_count ?? 0;
            $transfer->total_cost = (float) (
                $transfer->details_sum_total_cost ?? 0
            );

            return $transfer;
        });

        /*
        |--------------------------------------------------------------------------
        | Return Filters
        |--------------------------------------------------------------------------
        */

        $returnSearch = $request->input('return_search');
        $returnStatus = $request->input('return_status');
        $returnDateFrom = $request->input('return_date_from');
        $returnDateTo = $request->input('return_date_to');

        $returnPerPage = $request->integer('return_per_page', 10);

        $returnPerPage = in_array(
            $returnPerPage,
            [10, 25, 50, 100],
            true
        ) ? $returnPerPage : 10;

        $returnSortBy = $request->input(
            'return_sort_by',
            'transaction_date'
        );

        $returnSortDirection = strtolower(
            $request->input('return_sort_direction', 'desc')
        );

        $allowedReturnSorts = [
            'id',
            'number',
            'transaction_date',
            'status',
        ];

        if (! in_array($returnSortBy, $allowedReturnSorts, true)) {
            $returnSortBy = 'transaction_date';
        }

        if (! in_array($returnSortDirection, ['asc', 'desc'], true)) {
            $returnSortDirection = 'desc';
        }

        /*
        |--------------------------------------------------------------------------
        | Available Transfers For Return
        |--------------------------------------------------------------------------
        |
        | Hanya transfer Posted yang diterima oleh cabang aktif.
        | Jumlah yang bisa diretur dihitung dari kuantitas transfer dikurangi
        | kuantitas retur Draft, Submitted, Approved, dan Posted.
        |
        */

        $availableTransferQuery = StockTransferHeader::query()
            ->where('company_id', $companyId)
            ->where('to_branch_id', $activeBranchId)
            ->where('status', 'Posted')
            ->with([
                'fromBranch',
                'fromWarehouse',
                'toBranch',
                'toWarehouse',
                'details.variant.product',
                'details.unit',
            ])
            ->withCount('details')
            ->withSum('details', 'total_cost')
            ->withSum('details', 'qty');

        if ($returnDateFrom) {
            $availableTransferQuery->whereDate(
                'transaction_date',
                '>=',
                $returnDateFrom
            );
        }

        if ($returnDateTo) {
            $availableTransferQuery->whereDate(
                'transaction_date',
                '<=',
                $returnDateTo
            );
        }

        if ($request->filled('return_transfer_search')) {
            $search = $request->input('return_transfer_search');

            $availableTransferQuery->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $availableTransferQuery->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        /*
        |--------------------------------------------------------------------------
        | Load Candidate Transfers
        |--------------------------------------------------------------------------
        */

        $candidateTransfers = $availableTransferQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Reserved Return Quantities
        |--------------------------------------------------------------------------
        |
        | Aggregate per transfer detail agar tidak melakukan query per detail.
        |
        */

        $candidateTransferIds = $candidateTransfers
            ->pluck('id')
            ->all();

        $reservedQuantities = collect();

        if (! empty($candidateTransferIds)) {
            $reservedQuantities = DB::table(
                'stock_transfer_return_details as rd'
            )
                ->join(
                    'stock_transfer_return_headers as rh',
                    'rh.id',
                    '=',
                    'rd.stock_transfer_return_header_id'
                )
                ->whereIn(
                    'rh.stock_transfer_header_id',
                    $candidateTransferIds
                )
                ->where('rh.company_id', $companyId)
                ->whereIn('rh.status', [
                    'Draft',
                    'Submitted',
                    'Approved',
                    'Posted',
                ])
                ->whereNull('rh.deleted_at')
                ->select([
                    'rh.stock_transfer_header_id',
                    'rd.stock_transfer_detail_id',
                ])
                ->selectRaw('SUM(rd.qty) as reserved_qty')
                ->groupBy([
                    'rh.stock_transfer_header_id',
                    'rd.stock_transfer_detail_id',
                ])
                ->get()
                ->mapWithKeys(function ($row) {
                    return [
                        $row->stock_transfer_header_id
                            . ':'
                            . $row->stock_transfer_detail_id
                            => (float) $row->reserved_qty,
                    ];
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Stock Availability
        |--------------------------------------------------------------------------
        |
        | ProductStock menggunakan product_variant_id, unit_id,
        | warehouse_id, dan reseller_id sesuai pola modul stok.
        |
        */

        $warehouseIds = $candidateTransfers
            ->pluck('to_warehouse_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $stockAvailability = collect();

        if (! empty($warehouseIds)) {
            $stockAvailability = ProductStock::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $activeBranchId)
                ->whereIn('warehouse_id', $warehouseIds)
                ->whereNull('reseller_id')
                ->get([
                    'warehouse_id',
                    'product_variant_id',
                    'unit_id',
                    'available_qty',
                ])
                ->mapWithKeys(function ($stock) {
                    return [
                        $stock->warehouse_id
                            . ':'
                            . $stock->product_variant_id
                            . ':'
                            . $stock->unit_id
                            => (float) $stock->available_qty,
                    ];
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate Returnable Details
        |--------------------------------------------------------------------------
        */

        $eligibleTransfers = $candidateTransfers
            ->map(function ($transfer) use (
                $reservedQuantities,
                $stockAvailability
            ) {
                $returnableDetails = $transfer->details
                    ->map(function ($detail) use (
                        $transfer,
                        $reservedQuantities,
                        $stockAvailability
                    ) {
                        $reservedKey = $transfer->id
                            . ':'
                            . $detail->id;

                        $reservedQty = (float) (
                            $reservedQuantities->get($reservedKey, 0)
                        );

                        $originalQty = (float) $detail->qty;

                        $remainingQty = max(
                            0,
                            $originalQty - $reservedQty
                        );

                        $stockKey = $transfer->to_warehouse_id
                            . ':'
                            . $detail->product_variant_id
                            . ':'
                            . $detail->unit_id;

                        $availableQty = (float) (
                            $stockAvailability->get($stockKey, 0)
                        );

                        $maxReturnQty = min(
                            $remainingQty,
                            $availableQty
                        );

                        $detail->reserved_return_qty = $reservedQty;
                        $detail->remaining_return_qty = $remainingQty;
                        $detail->available_stock_qty = $availableQty;
                        $detail->max_return_qty = $maxReturnQty;

                        return $detail;
                    });

                $transfer->setRelation('details', $returnableDetails);

                $transfer->details_count = $returnableDetails->count();

                $transfer->total_qty = (float) $returnableDetails->sum(
                    fn ($detail) => (float) $detail->qty
                );

                $transfer->total_cost = (float) $returnableDetails->sum(
                    fn ($detail) => (float) $detail->total_cost
                );

                $transfer->returnable_qty = (float) $returnableDetails->sum(
                    fn ($detail) => (float) $detail->max_return_qty
                );

                $transfer->has_previous_returns = $returnableDetails->contains(
                    fn ($detail) => (float) $detail->reserved_return_qty > 0
                );

                $transfer->returnable_details_count = $returnableDetails
                    ->filter(fn ($detail) =>
                        (float) $detail->max_return_qty > 0
                    )
                    ->count();

                return $transfer;
            })
            ->filter(fn ($transfer) =>
                $transfer->returnable_details_count > 0
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Available Transfer Statistics
        |--------------------------------------------------------------------------
        */

       $transferStatistics = [
            'total' => $candidateTransfers->count(),

            'available' => $eligibleTransfers->count(),

            'partiallyReturned' => $eligibleTransfers
                ->filter(fn ($transfer) =>
                    $transfer->has_previous_returns
                )
                ->count(),

            'totalAmount' => (float) $eligibleTransfers->sum(
                fn ($transfer) => (float) $transfer->total_cost
            ),
        ];
        /*
        |--------------------------------------------------------------------------
        | Available Transfers Pagination
        |--------------------------------------------------------------------------
        */

        $returnTransferPage = max(
            1,
            $request->integer('return_transfer_page', 1)
        );

        $availableTransfers = new LengthAwarePaginator(
            $eligibleTransfers
                ->forPage($returnTransferPage, $returnPerPage)
                ->values(),
            $eligibleTransfers->count(),
            $returnPerPage,
            $returnTransferPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'return_transfer_page',
            ]
        );

         /*
        |--------------------------------------------------------------------------
        | Return History Query
        |--------------------------------------------------------------------------
        */

        $returnHistoryQuery = StockTransferReturnHeader::query()
            ->where('company_id', $companyId)
            ->where('from_branch_id', $activeBranchId)
            ->with([
                'stockTransferHeader.fromBranch',
                'stockTransferHeader.toBranch',
                'fromBranch',
                'fromWarehouse',
                'toBranch',
                'toWarehouse',
                'creator',
                'details.variant.product',
                'details.unit',
            ])
            ->withCount('details')
            ->withSum('details', 'total_cost')
            ->withSum('details', 'qty');

        if ($returnSearch) {
            $returnHistoryQuery->where(function ($q) use ($returnSearch) {
                $q->where('number', 'like', "%{$returnSearch}%")
                    ->orWhereHas(
                        'stockTransferHeader',
                        fn ($transferQuery) => $transferQuery->where(
                            'number',
                            'like',
                            "%{$returnSearch}%"
                        )
                    );
            });
        }

        if ($returnStatus && strtolower($returnStatus) !== 'all') {
            $returnHistoryQuery->where('status', $returnStatus);
        }

        if ($returnDateFrom) {
            $returnHistoryQuery->whereDate(
                'transaction_date',
                '>=',
                $returnDateFrom
            );
        }

        if ($returnDateTo) {
            $returnHistoryQuery->whereDate(
                'transaction_date',
                '<=',
                $returnDateTo
            );
        }

        $returnHistoryQuery->orderBy(
            $returnSortBy,
            $returnSortDirection
        );

        /*
        |--------------------------------------------------------------------------
        | Return History Statistics
        |--------------------------------------------------------------------------
        |
        | Statistik mengikuti filter tanggal, pencarian, dan status.
        | Pagination tidak memengaruhi statistik.
        |
        */

        $returnStatisticsQuery = clone $returnHistoryQuery;

        $returnStatistics = [
            'total' => (clone $returnStatisticsQuery)->count(),

            'draft' => (clone $returnStatisticsQuery)
                ->where('status', 'Draft')
                ->count(),

            'rejected' => (clone $returnStatisticsQuery)
                ->where('status', 'Rejected')
                ->count(),

            'posted' => (clone $returnStatisticsQuery)
                ->where('status', 'Posted')
                ->count(),

            'totalAmount' => (float) (clone $returnStatisticsQuery)
                ->reorder()
                ->sum(
                    DB::raw(
                        '(SELECT COALESCE(SUM(rd.total_cost), 0)
                          FROM stock_transfer_return_details rd
                          WHERE rd.stock_transfer_return_header_id = stock_transfer_return_headers.id)'
                    )
                ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Return History Pagination
        |--------------------------------------------------------------------------
        */

        $returns = $returnHistoryQuery
            ->paginate(
                $returnPerPage,
                ['*'],
                'return_page'
            )
            ->withQueryString();

        $returns->getCollection()->transform(function ($return) {
            $return->total_cost = (float) (
                $return->details_sum_total_cost ?? 0
            );

            $return->total_qty = (float) (
                $return->details_sum_qty ?? 0
            );

            return $return;
        });

        /*
        |--------------------------------------------------------------------------
        | Master Data
        |--------------------------------------------------------------------------
        */

        $branches = Branch::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get([
                'id',
                'company_id',
                'name',
            ])
            ->map(fn ($branch) => [
                'id' => $branch->id,
                'company_id' => $branch->company_id,
                'name' => $branch->name,
                'label' => $branch->name,
            ])
            ->values();

        $warehouses = Warehouse::query()
            ->orderBy('name')
            ->get()
            ->map(fn ($warehouse) => [
                'id' => $warehouse->id,
                'label' => $warehouse->name,
                'branch_id' => $warehouse->branch_id,
            ])
            ->values();

        $variants = ProductVariant::query()
            ->active()
            ->whereHas('units', function ($query) {
                $query->active();
            })
            ->with([
                'product',
                'units' => function ($query) {
                    $query
                        ->active()
                        ->with('unit')
                        ->orderBy('sort_order');
                },
            ])
            ->orderBy('sku')
            ->get([
                'id',
                'product_id',
                'sku',
                'name',
            ])
            ->map(fn ($variant) => [
                'id' => $variant->id,
                'label' => implode(
                    ' - ',
                    array_filter([
                        $variant->product?->name,
                        $variant->name,
                    ])
                ),
                'units' => $variant->units
                    ->map(fn ($variantUnit) => [
                        'id' => $variantUnit->unit_id,
                        'label' => $variantUnit->unit?->name,
                        'conversion_factor' => $variantUnit->conversion_factor,
                        'is_base' => $variantUnit->is_base,
                        'is_default' => $variantUnit->is_default,
                    ])
                    ->values(),
            ])
            ->values();

        $units = Unit::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Preview Number
        |--------------------------------------------------------------------------
        */

        $previewNumber = $this->codeGeneratorService
            ->preview('stock_transfer');

        $previewReturnNumber = $this->codeGeneratorService
            ->preview('stock_transfer_return');

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters = $request->only([
            'search',
            'branch_id',
            'warehouse_id',
            'status',
            'date_from',
            'date_to',
            'per_page',
            'sort_by',
            'sort_direction',
        ]);

        $returnFilters = [
            'search' => $returnSearch,
            'status' => $returnStatus,
            'date_from' => $returnDateFrom,
            'date_to' => $returnDateTo,
            'per_page' => $returnPerPage,
            'sort_by' => $returnSortBy,
            'sort_direction' => $returnSortDirection,
        ];

        /*
        |--------------------------------------------------------------------------
        | Dashboard Kit Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Inventory/Transfer/Index',
            [
                'title' => 'Stock Transfer',

                'transfers' => $transfers,
                'statistics' => $statistics,

                'availableTransfers' => $availableTransfers,
                'returns' => $returns,

                'transferStatistics' => $transferStatistics,
                'returnStatistics' => $returnStatistics,

                'transferFilters' => [
                    'search' => $request->input('return_transfer_search'),
                    'date_from' => $returnDateFrom,
                    'date_to' => $returnDateTo,
                    'per_page' => $returnPerPage,
                ],

                'returnFilters' => $returnFilters,

                'returnDateFrom' => $returnDateFrom,
                'returnDateTo' => $returnDateTo,

                'branches' => $branches,
                'warehouses' => $warehouses,
                'variants' => $variants,
                'units' => $units,

                'previewNumber' => $previewNumber,
                'previewReturnNumber' => $previewReturnNumber,

                'filters' => $filters,
                'activeBranchId' => $activeBranchId,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(StockTransferHeader $stockTransfer)
    {
        $this->inventoryService->submitStockTransfer($stockTransfer);

        return redirect()
            ->back()
            ->with('success', 'Stock transfer submitted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(StockTransferHeader $stockTransfer)
    {
        $this->inventoryService->approveStockTransfer($stockTransfer);

        return redirect()
            ->back()
            ->with('success', 'Stock transfer approved successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render(
            'Inventory/Transfer/Create',
            [
                'title' => 'Create Stock Transfer',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(StoreStockTransferRequest $request)
    {
        $transfer = $this->inventoryService->createStockTransfer(
            $request->validated()
        );

        return redirect()
            ->route('stock-transfers.index', $transfer)
            ->with('success', 'Stock transfer created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(StockTransferHeader $stockTransfer)
    {
        $stockTransfer->load([
            'company',
            'fromBranch',
            'fromWarehouse',
            'toBranch',
            'toWarehouse',
            'creator',
            'updater',
            'poster',
            'rejector',
            'deleter',
            'details.variant.product',
            'details.unit',
            'movements',
            'activities',
        ]);

        return Inertia::render(
            'Inventory/Transfer/Show',
            [
                'transfer' => $stockTransfer,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateStockTransferRequest $request,
        StockTransferHeader $stockTransfer
    ) {
        $this->inventoryService->updateStockTransfer(
            $stockTransfer,
            $request->validated()
        );

        return redirect()
            ->back()
            ->with('success', 'Stock transfer updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(StockTransferHeader $stockTransfer)
    {
        $this->inventoryService->postStockTransfer($stockTransfer);

        return redirect()
            ->route('stock-transfers.index', $stockTransfer)
            ->with('success', 'Stock transfer posted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel / Reject
    |--------------------------------------------------------------------------
    */

    public function cancel(
        CancelStockTransferRequest $request,
        StockTransferHeader $stockTransfer
    ) {
        $this->inventoryService->cancelStockTransfer(
            $stockTransfer,
            $request->validated()['reason']
        );

        return redirect()
            ->back()
            ->with('success', 'Stock transfer rejected successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel Approved Transfer
    |--------------------------------------------------------------------------
    */

    public function cancelTransfer(
        Request $request,
        StockTransferHeader $stockTransfer
    ) {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $this->inventoryService->cancelApprovedStockTransfer(
            $stockTransfer,
            $validated['reason']
        );

        return redirect()
            ->back()
            ->with('success', 'Stock transfer cancelled successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Warehouse Stocks
    |--------------------------------------------------------------------------
    */

    public function getWarehouseStocks(Warehouse $warehouse)
    {
        $stocks = ProductStock::query()
            ->with(['variant.product', 'unit'])
            ->where('warehouse_id', $warehouse->id)
            ->whereNull('reseller_id')
            ->orderBy('product_variant_id')
            ->get();

        return response()->json([
            'data' => $stocks,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    public function data(StockTransferHeader $stockTransfer)
    {
        $stockTransfer->load([
            'fromBranch',
            'fromWarehouse',
            'toBranch',
            'toWarehouse',
            'creator',
            'updater',
            'poster',
            'rejector',
            'deleter',
            'details.variant.product',
            'details.unit',
            'movements.productVariant.product',
            'movements.unit',
            'activities.performer',
        ]);

        return response()->json([
            'data' => $stockTransfer,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate
    |--------------------------------------------------------------------------
    */

    public function duplicate(StockTransferHeader $stockTransfer)
    {
        $duplicate = $this->inventoryService->duplicateStockTransfer(
            $stockTransfer
        );

        return redirect()
            ->route('stock-transfers.index', $duplicate)
            ->with('success', 'Stock transfer duplicated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(StockTransferHeader $stockTransfer)
    {
        $this->inventoryService->deleteStockTransfers([
            $stockTransfer->id,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Stock transfer deleted successfully.');
    }
}