<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\StockTransferHeader;
use App\Models\Inventory\StockTransferReturnHeader;
use App\Services\Core\CodeGeneratorService;
use App\Services\Inventory\StockTransferReturnService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StockTransferReturnController extends Controller
{
    public function __construct(
        protected StockTransferReturnService $returnService,
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Access Helpers
    |--------------------------------------------------------------------------
    */

    private function companyId(): int
    {
        return (int) auth()->user()->company_id;
    }

    private function activeBranchId(): int
    {
        return (int) session('current_branch_id');
    }

    private function returnQuery(): Builder
    {
        return StockTransferReturnHeader::query()
            ->where('company_id', $this->companyId())
            ->where('from_branch_id', $this->activeBranchId());
    }

    private function authorizeReturn(
        StockTransferReturnHeader $stockTransferReturn
    ): StockTransferReturnHeader {
        abort_if(
            ! $this->activeBranchId(),
            422,
            'Active branch has not been selected.'
        );

        abort_unless(
            (int) $stockTransferReturn->company_id === $this->companyId()
            && (int) $stockTransferReturn->from_branch_id === $this->activeBranchId(),
            404
        );

        return $stockTransferReturn;
    }

    private function authorizeOriginalTransfer(
        StockTransferHeader $transfer
    ): void {
        abort_if(
            ! $this->activeBranchId(),
            422,
            'Active branch has not been selected.'
        );

        abort_unless(
            (int) $transfer->company_id === $this->companyId()
            && (int) $transfer->to_branch_id === $this->activeBranchId()
            && $transfer->status === 'Posted',
            422,
            'The selected transfer is not available for return.'
        );
    }

    private function reservedReturnQuantities(int $transferId): array
    {
        return DB::table('stock_transfer_return_details')
            ->join(
                'stock_transfer_return_headers',
                'stock_transfer_return_headers.id',
                '=',
                'stock_transfer_return_details.stock_transfer_return_header_id'
            )
            ->where(
                'stock_transfer_return_headers.stock_transfer_header_id',
                $transferId
            )
            ->where(
                'stock_transfer_return_headers.company_id',
                $this->companyId()
            )
            ->whereIn('stock_transfer_return_headers.status', [
                'Draft',
                'Submitted',
                'Approved',
                'Posted',
            ])
            ->whereNull('stock_transfer_return_headers.deleted_at')
            ->select(
                'stock_transfer_return_details.stock_transfer_detail_id'
            )
            ->selectRaw(
                'SUM(stock_transfer_return_details.qty) AS reserved_qty'
            )
            ->groupBy(
                'stock_transfer_return_details.stock_transfer_detail_id'
            )
            ->pluck('reserved_qty', 'stock_transfer_detail_id')
            ->map(fn ($qty) => (float) $qty)
            ->all();
    }

    private function stockAvailabilityMap(
        StockTransferHeader $transfer
    ): array {
        return ProductStock::query()
            ->where('company_id', $transfer->company_id)
            ->where('branch_id', $transfer->to_branch_id)
            ->where('warehouse_id', $transfer->to_warehouse_id)
            ->whereNull('reseller_id')
            ->get([
                'product_variant_id',
                'unit_id',
                'available_qty',
            ])
            ->keyBy(
                fn ($stock) =>
                    $stock->product_variant_id . ':' . $stock->unit_id
            )
            ->map(fn ($stock) => (float) $stock->available_qty)
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        abort_if(
            ! $this->activeBranchId(),
            422,
            'Active branch has not been selected.'
        );

        $query = $this->returnQuery()
            ->with([
                'stockTransferHeader',
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
            $search = trim($request->input('search'));

            $query->where(function ($query) use ($search) {
                $query
                    ->where('number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas(
                        'stockTransferHeader',
                        fn ($transfer) => $transfer->where(
                            'number',
                            'like',
                            "%{$search}%"
                        )
                    );
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->input('status')
            );
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
        ];

        $sortBy = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection);

        /*
         * Calculate statistics from the same company and branch scope,
         * independent of the current search, status, and date filters.
         */
        $statisticsQuery = $this->returnQuery();

        $statistics = [
            'total' => (clone $statisticsQuery)->count(),

            'draft' => (clone $statisticsQuery)
                ->where('status', 'Draft')
                ->count(),

            'submitted' => (clone $statisticsQuery)
                ->where('status', 'Submitted')
                ->count(),

            'approved' => (clone $statisticsQuery)
                ->where('status', 'Approved')
                ->count(),

            'posted' => (clone $statisticsQuery)
                ->where('status', 'Posted')
                ->count(),

            'rejected' => (clone $statisticsQuery)
                ->where('status', 'Rejected')
                ->count(),

            'cancelled' => (clone $statisticsQuery)
                ->where('status', 'Cancelled')
                ->count(),
        ];

        $perPage = $request->integer('per_page', 10);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $returns = $query
            ->paginate($perPage)
            ->withQueryString();

        $returns->getCollection()->transform(function ($return) {
            $return->details_count = $return->details_count ?? 0;
            $return->total_cost = $return->details_sum_total_cost ?? 0;

            return $return;
        });

        return Inertia::render('Inventory/Transfer/Returns/Index', [
            'title' => 'Stock Transfer Return',
            'returns' => $returns,
            'statistics' => $statistics,
            'activeBranchId' => $this->activeBranchId(),

            'filters' => $request->only([
                'search',
                'status',
                'date_from',
                'date_to',
                'per_page',
                'sort_by',
                'sort_direction',
            ]),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        abort_if(
            ! $this->activeBranchId(),
            422,
            'Active branch has not been selected.'
        );

        $transfers = StockTransferHeader::query()
            ->with([
                'fromBranch',
                'fromWarehouse',
                'toBranch',
                'toWarehouse',
                'details.variant.product',
                'details.unit',
            ])
            ->where('company_id', $this->companyId())
            ->where('to_branch_id', $this->activeBranchId())
            ->where('status', 'Posted')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($transfer) {
                $reservedQuantities = $this->reservedReturnQuantities(
                    (int) $transfer->id
                );

                $stockMap = $this->stockAvailabilityMap($transfer);

                $details = $transfer->details
                    ->map(function ($detail) use (
                        $reservedQuantities,
                        $stockMap
                    ) {
                        $reservedQty = (float) (
                            $reservedQuantities[$detail->id] ?? 0
                        );

                        $originalQty = (float) $detail->qty;

                        $remainingQty = max(
                            0,
                            $originalQty - $reservedQty
                        );

                        $stockKey = $detail->product_variant_id
                            . ':'
                            . $detail->unit_id;

                        $availableQty = (float) (
                            $stockMap[$stockKey] ?? 0
                        );

                        $maxReturnQty = min(
                            $remainingQty,
                            $availableQty
                        );

                        return [
                            'stock_transfer_detail_id' => $detail->id,
                            'product_variant_id' => $detail->product_variant_id,
                            'product_name' => $detail->variant?->product?->name,
                            'variant_name' => $detail->variant?->name,
                            'sku' => $detail->variant?->sku,
                            'unit_id' => $detail->unit_id,
                            'unit_name' => $detail->unit?->name,

                            'original_qty' => $originalQty,
                            'returned_qty' => $reservedQty,
                            'remaining_qty' => $remainingQty,
                            'available_qty' => $availableQty,
                            'max_return_qty' => $maxReturnQty,

                            'unit_cost' => (float) $detail->unit_cost,
                        ];
                    })
                    ->values();

                return [
                    'id' => $transfer->id,
                    'number' => $transfer->number,

                    'transaction_date' =>
                        $transfer->transaction_date?->format('Y-m-d'),

                    'from_branch' => $transfer->fromBranch?->name,
                    'from_warehouse' => $transfer->fromWarehouse?->name,
                    'to_branch' => $transfer->toBranch?->name,
                    'to_warehouse' => $transfer->toWarehouse?->name,

                    'details' => $details,
                ];
            })
            ->filter(
                fn ($transfer) => $transfer['details']->contains(
                    fn ($detail) => $detail['max_return_qty'] > 0
                )
            )
            ->values();

        return Inertia::render('Inventory/Transfer/Returns/Create', [
            'title' => 'Create Stock Transfer Return',

            'previewNumber' => $this->codeGeneratorService
                ->preview('stock_transfer_return'),

            'transfers' => $transfers,
            'activeBranchId' => $this->activeBranchId(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        abort_if(
            ! $this->activeBranchId(),
            422,
            'Active branch has not been selected.'
        );

        $validated = $request->validate([
            'stock_transfer_header_id' => [
                'required',
                'integer',
                'exists:stock_transfer_headers,id',
            ],

            'transaction_date' => [
                'required',
                'date',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'details' => [
                'required',
                'array',
                'min:1',
            ],

            'details.*.stock_transfer_detail_id' => [
                'required',
                'integer',
                'distinct',
            ],

            'details.*.qty' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'details.*.description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $transfer = StockTransferHeader::query()
            ->where('company_id', $this->companyId())
            ->where('to_branch_id', $this->activeBranchId())
            ->where('status', 'Posted')
            ->findOrFail($validated['stock_transfer_header_id']);

        $this->authorizeOriginalTransfer($transfer);

        $this->returnService->createReturn($validated);

        return back();
        
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        StockTransferReturnHeader $stockTransferReturn
    ) {
        $this->authorizeReturn($stockTransferReturn);

        $stockTransferReturn->load([
            'stockTransferHeader.fromBranch',
            'stockTransferHeader.toBranch',
            'stockTransferHeader.fromWarehouse',
            'stockTransferHeader.toWarehouse',
            'fromBranch',
            'fromWarehouse',
            'toBranch',
            'toWarehouse',
            'creator',
            'updater',
            'poster',
            'rejector',
            'canceller',
            'deleter',
            'details.variant.product',
            'details.unit',
            'activities.performer',
            'movements',
            'sourceJournalEntry',
            'destinationJournalEntry',
        ]);

        return Inertia::render('Inventory/TransferReturn/Show', [
            'return' => $stockTransferReturn,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        StockTransferReturnHeader $stockTransferReturn
    ) {
        $this->authorizeReturn($stockTransferReturn);

        $this->returnService->submitReturn($stockTransferReturn);

        return back()->with(
            'success',
            'Stock transfer return submitted successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        StockTransferReturnHeader $stockTransferReturn
    ) {
        $this->authorizeReturn($stockTransferReturn);

        $this->returnService->approveReturn($stockTransferReturn);

        return back()->with(
            'success',
            'Stock transfer return approved successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        StockTransferReturnHeader $stockTransferReturn
    ) {
        $this->authorizeReturn($stockTransferReturn);

        $this->returnService->postReturn($stockTransferReturn);

        return back()->with(
            'success',
            'Stock transfer return posted successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        Request $request,
        StockTransferReturnHeader $stockTransferReturn
    ) {
        $this->authorizeReturn($stockTransferReturn);

        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $this->returnService->rejectReturn(
            $stockTransferReturn,
            $validated['reason']
        );

        return back()->with(
            'success',
            'Stock transfer return rejected successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        StockTransferReturnHeader $stockTransferReturn
    ) {
        $this->authorizeReturn($stockTransferReturn);

        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $this->returnService->cancelReturn(
            $stockTransferReturn,
            $validated['reason']
        );

        return back()->with(
            'success',
            'Stock transfer return cancelled successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    public function data(
        StockTransferReturnHeader $stockTransferReturn
    ) {
        $this->authorizeReturn($stockTransferReturn);

        $stockTransferReturn->load([
            'stockTransferHeader',
            'fromBranch',
            'fromWarehouse',
            'toBranch',
            'toWarehouse',
            'creator',
            'updater',
            'poster',
            'rejector',
            'canceller',
            'deleter',
            'details.variant.product',
            'details.unit',
            'movements',
            'activities.performer',
            'sourceJournalEntry',
            'destinationJournalEntry',
        ]);

        return response()->json([
            'data' => $stockTransferReturn,
        ]);
    }
}