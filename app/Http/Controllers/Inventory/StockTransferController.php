<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Unit;
use App\Models\MasterData\Warehouse;
use App\Models\Product\ProductVariant;
use App\Services\Inventory\InventoryService;
use App\Services\Core\CodeGeneratorService;

use App\Http\Requests\Inventory\StockTransfer\StoreStockTransferRequest;
use App\Http\Requests\Inventory\StockTransfer\UpdateStockTransferRequest;
use App\Http\Requests\Inventory\StockTransfer\CancelStockTransferRequest;
use App\Models\Inventory\StockTransferHeader;
use App\Models\Inventory\ProductStock;


class StockTransferController extends Controller
{
    public function __construct(
    protected InventoryService $inventoryService,
    protected CodeGeneratorService $codeGeneratorService
) {
}


    /*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

public function index(Request $request)
{
    /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    */

    $query =
        StockTransferHeader::query()
            ->with([
                'fromBranch',
                'fromWarehouse',
                'toBranch',
                'toWarehouse',
                'creator',
                'details',
            ])
            ->withCount(
                'details'
            )
            ->withSum(
                'details',
                'total_cost'
            );


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    $query->when(
        $request->filled('search'),
        function ($query) use ($request) {

            $search =
                $request->search;

            $query->where(function ($query) use (
                $search
            ) {

                $query
                    ->where(
                        'number',
                        'like',
                        "%{$search}%"
                    )

                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    )

                    ->orWhereHas(
                        'fromBranch',
                        function ($branch) use (
                            $search
                        ) {

                            $branch->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );

                        }
                    )

                    ->orWhereHas(
                        'toBranch',
                        function ($branch) use (
                            $search
                        ) {

                            $branch->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );

                        }
                    )

                    ->orWhereHas(
                        'fromWarehouse',
                        function ($warehouse) use (
                            $search
                        ) {

                            $warehouse->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );

                        }
                    )

                    ->orWhereHas(
                        'toWarehouse',
                        function ($warehouse) use (
                            $search
                        ) {

                            $warehouse->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );

                        }
                    );

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Branch Filter
    |--------------------------------------------------------------------------
    */

    $query->when(
        $request->filled('branch_id'),
        function ($query) use ($request) {

            $branchId =
                $request->branch_id;

            $query->where(function ($query) use (
                $branchId
            ) {

                $query
                    ->where(
                        'from_branch_id',
                        $branchId
                    )
                    ->orWhere(
                        'to_branch_id',
                        $branchId
                    );

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Warehouse Filter
    |--------------------------------------------------------------------------
    */

    $query->when(
        $request->filled('warehouse_id'),
        function ($query) use ($request) {

            $warehouseId =
                $request->warehouse_id;

            $query->where(function ($query) use (
                $warehouseId
            ) {

                $query
                    ->where(
                        'from_warehouse_id',
                        $warehouseId
                    )
                    ->orWhere(
                        'to_warehouse_id',
                        $warehouseId
                    );

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    $query->when(
        $request->filled('status'),
        function ($query) use ($request) {

            $query->where(
                'status',
                $request->status
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Date Filter
    |--------------------------------------------------------------------------
    */

    $query->when(
        $request->filled('date_from'),
        function ($query) use ($request) {

            $query->whereDate(
                'transaction_date',
                '>=',
                $request->date_from
            );

        }
    );


    $query->when(
        $request->filled('date_to'),
        function ($query) use ($request) {

            $query->whereDate(
                'transaction_date',
                '<=',
                $request->date_to
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */

    $sortBy =
        $request->input(
            'sort_by',
            'id'
        );

    $sortDirection =
        $request->input(
            'sort_direction',
            'desc'
        );


    $allowedSorts = [

        'id',

        'number',

        'transaction_date',

        'status',

        'total_cost',

    ];


    if (
        ! in_array(
            $sortBy,
            $allowedSorts,
            true
        )
    ) {

        $sortBy = 'id';

    }


    if (
        ! in_array(
            $sortDirection,
            [
                'asc',
                'desc',
            ],
            true
        )
    ) {

        $sortDirection = 'desc';

    }


    if (
        $sortBy === 'total_cost'
    ) {

        $query->orderBy(
            'details_sum_total_cost',
            $sortDirection
        );

    } else {

        $query->orderBy(
            $sortBy,
            $sortDirection
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    |
    | Clone BEFORE pagination.
    |
    */

    $statisticsQuery =
        clone $query;


    $statistics = [

        'total' =>
            (clone $statisticsQuery)
                ->count(),

        'draft' =>
            (clone $statisticsQuery)
                ->where(
                    'status',
                    'Draft'
                )
                ->count(),

        'rejected' =>
            (clone $statisticsQuery)
                ->where(
                    'status',
                    'Rejected'
                )
                ->count(),

        'posted' =>
            (clone $statisticsQuery)
                ->where(
                    'status',
                    'Posted'
                )
                ->count(),

    ];


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $transfers =
        $query
            ->paginate(
                $request->integer(
                    'per_page',
                    10
                )
            )
            ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | Transform
    |--------------------------------------------------------------------------
    */

    $transfers
        ->getCollection()
        ->transform(
            function ($transfer) {

                $transfer->details_count =
                    $transfer->details_count
                    ?? 0;

                $transfer->total_cost =
                    $transfer->details_sum_total_cost
                    ?? 0;

                return $transfer;

            }
        );


    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */

    $branches =
        Branch::query()
            ->orderBy('name')
            ->get()
            ->map(function ($branch) {

                return [
                    'id' =>
                        $branch->id,

                    'label' =>
                        $branch->name,

                ];

            })
            ->values();


    $warehouses =
        Warehouse::query()
            ->orderBy('name')
            ->get()
            ->map(function ($warehouse) {

                return [
                    'id' =>
                        $warehouse->id,

                    'label' =>
                        $warehouse->name,

                    'branch_id' =>
                        $warehouse->branch_id,

                ];

            })
            ->values();


    $variants =
        ProductVariant::query()
            ->with('product')
            ->orderBy('id')
            ->get();


    $units =
        Unit::query()
            ->orderBy('name')
            ->get();


    /*
    |--------------------------------------------------------------------------
    | Preview Number
    |--------------------------------------------------------------------------
    */

    $previewNumber =
        $this->codeGeneratorService
            ->preview(
                'stock_transfer'
            );


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return Inertia::render(
    'Inventory/Transfer/Index',
    [

        'title' =>
            'Stock Transfer',

        'transfers' =>
            $transfers,

        'statistics' =>
            $statistics,

                'branches' =>
            \App\Models\MasterData\Branch::query()
                ->orderBy('name')
                ->get()
                ->map(fn ($branch) => [
                    'id' => $branch->id,
                    'label' => $branch->name,
                ])
                ->values(),

        'warehouses' =>
            Warehouse::query()
                ->orderBy('name')
                ->get()
                ->map(fn ($warehouse) => [
                    'id' => $warehouse->id,
                    'label' => $warehouse->name,
                    'branch_id' => $warehouse->branch_id,
                ])
                ->values(),

       'variants' =>
    \App\Models\Product\ProductVariant::query()
        ->active()
        ->whereHas(
            'units',
            function ($query) {

                $query->active();

            }
        )
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

            'id' =>
                $variant->id,

            'label' =>
                implode(
                    ' - ',
                    array_filter([
                        $variant->product?->name,
                        $variant->name,
                    ])
                ),

            'units' =>
                $variant->units
                    ->map(fn ($variantUnit) => [

                        'id' =>
                            $variantUnit->unit_id,

                        'label' =>
                            $variantUnit->unit?->name,

                        'conversion_factor' =>
                            $variantUnit->conversion_factor,

                        'is_base' =>
                            $variantUnit->is_base,

                        'is_default' =>
                            $variantUnit->is_default,

                    ])
                    ->values(),

        ])
        ->values(),

        'previewNumber' =>
            $this->codeGeneratorService
                ->preview('stock_transfer'),

        'filters' =>
            $request->only([
                'search',
                'status',
                'per_page',
                'sort_by',
                'sort_direction',
            ]),

    ]
);
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

                'title' =>
                    'Create Stock Transfer',

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreStockTransferRequest $request
    ) {

        $transfer =
            $this->inventoryService
                ->createStockTransfer(
                    $request->validated()
                );


        return redirect()
            ->route(
                'stock-transfers.index',
                $transfer
            )
            ->with(
                'success',
                'Stock transfer created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        StockTransferHeader $stockTransfer
    ) {

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

                'transfer' =>
                    $stockTransfer,

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

        $this->inventoryService
            ->updateStockTransfer(
                $stockTransfer,
                $request->validated()
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Stock transfer updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        StockTransferHeader $stockTransfer
    ) {

        $this->inventoryService
            ->postStockTransfer(
                $stockTransfer
            );


        return redirect()
            ->route(
                'stock-transfers.show',
                $stockTransfer
            )
            ->with(
                'success',
                'Stock transfer posted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        CancelStockTransferRequest $request,
        StockTransferHeader $stockTransfer
    ) {

        $this->inventoryService
            ->cancelStockTransfer(
                $stockTransfer,
                $request->validated()['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Stock transfer rejected successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Warehouse Stocks
    |--------------------------------------------------------------------------
    */

    public function getWarehouseStocks(
    Warehouse $warehouse
) {

    $stocks = ProductStock::query()
        ->with([
            'variant.product',
            'unit',
        ])
        ->where(
            'warehouse_id',
            $warehouse->id
        )
        ->orderBy(
            'product_variant_id'
        )
        ->get();

    return response()->json([
        'data' => $stocks,
    ]);
}
    /*
|--------------------------------------------------------------------------
| Duplicate
|--------------------------------------------------------------------------
*/

public function duplicate(
    StockTransferHeader $stockTransfer
) {

    $duplicate =
        $this->inventoryService
            ->duplicateStockTransfer(
                $stockTransfer
            );


    return redirect()
        ->route(
            'stock-transfers.index',
            $duplicate
        )
        ->with(
            'success',
            'Stock transfer duplicated successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

public function destroy(
    StockTransferHeader $stockTransfer
) {

    $this->inventoryService
        ->deleteStockTransfers([
            $stockTransfer->id,
        ]);


    return redirect()
        ->back()
        ->with(
            'success',
            'Stock transfer deleted successfully.'
        );
}
}