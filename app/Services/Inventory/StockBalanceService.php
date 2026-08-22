<?php

namespace App\Services\Inventory;
use App\Models\MasterData\ProductVariantPrice;
use App\Models\Inventory\ProductStock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Models\MasterData\PriceType;
use App\Models\Inventory\InventoryMovement;
use App\Services\Inventory\InventoryCostingService;
use App\Models\MasterData\Company;
class StockBalanceService
{
    protected InventoryCostingService $inventoryCostingService;

    public function __construct(
        InventoryCostingService $inventoryCostingService
    ) {
        $this->inventoryCostingService =
            $inventoryCostingService;
    }
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function getStockBalance(
        array $filters = []
    ): LengthAwarePaginator {

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query =
                ProductStock::query()
                    ->with([
                        'branch',
                        'warehouse',

                        'variant.product',

                        'variant.units' => function ($query) {

                            $query
                                ->active()
                                ->with('unit')
                                ->orderBy('sort_order');

                        },

                        'unit',
                    ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (
            ! empty($filters['search'])
        ) {

            $search =
                $filters['search'];

            $query->where(
                function (Builder $query) use ($search) {

                    $query
                        ->whereHas(
                            'variant',
                            function (Builder $variant) use ($search) {

                                $variant
                                    ->where(
                                        'sku',
                                        'like',
                                        "%{$search}%"
                                    )

                                    ->orWhere(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )

                                    ->orWhereHas(
                                        'product',
                                        function (Builder $product) use ($search) {

                                            $product->where(
                                                'name',
                                                'like',
                                                "%{$search}%"
                                            );

                                        }
                                    );

                            }
                        )

                        ->orWhereHas(
                            'branch',
                            function (Builder $branch) use ($search) {

                                $branch->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );

                            }
                        )

                        ->orWhereHas(
                            'warehouse',
                            function (Builder $warehouse) use ($search) {

                                $warehouse->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );

                            }
                        );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        */

        $query->when(
            ! empty($filters['branch_id']),
            function ($query) use ($filters) {

                $query->where(
                    'branch_id',
                    $filters['branch_id']
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Warehouse
        |--------------------------------------------------------------------------
        */

        $query->when(
            ! empty($filters['warehouse_id']),
            function ($query) use ($filters) {

                $query->where(
                    'warehouse_id',
                    $filters['warehouse_id']
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Product Variant
        |--------------------------------------------------------------------------
        */

        $query->when(
            ! empty($filters['product_variant_id']),
            function ($query) use ($filters) {

                $query->where(
                    'product_variant_id',
                    $filters['product_variant_id']
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Unit
        |--------------------------------------------------------------------------
        */

        $query->when(
            ! empty($filters['unit_id']),
            function ($query) use ($filters) {

                $query->where(
                    'unit_id',
                    $filters['unit_id']
                );

            }
        );
            /*
            |--------------------------------------------------------------------------
            | Historical Stock Balance
            |--------------------------------------------------------------------------
            */

            $hasDateFilter =
                ! empty($filters['date_to']);

            if ($hasDateFilter) {

                $movementQuery =
                    InventoryMovement::query()
                        ->with([
                            'variant.product',
                            'unit',
                            'branch',
                            'warehouse',
                        ])
                        ->whereDate(
                            'transaction_date',
                            '<=',
                            $filters['date_to']
                        );


                /*
                |--------------------------------------------------------------------------
                | Branch
                |--------------------------------------------------------------------------
                */

                $movementQuery->when(
                    ! empty($filters['branch_id']),
                    function ($query) use ($filters) {

                        $query->where(
                            'branch_id',
                            $filters['branch_id']
                        );

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Warehouse
                |--------------------------------------------------------------------------
                */

                $movementQuery->when(
                    ! empty($filters['warehouse_id']),
                    function ($query) use ($filters) {

                        $query->where(
                            'warehouse_id',
                            $filters['warehouse_id']
                        );

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Product Variant
                |--------------------------------------------------------------------------
                */

                $movementQuery->when(
                    ! empty($filters['product_variant_id']),
                    function ($query) use ($filters) {

                        $query->where(
                            'product_variant_id',
                            $filters['product_variant_id']
                        );

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | Unit
                |--------------------------------------------------------------------------
                */

                $movementQuery->when(
                    ! empty($filters['unit_id']),
                    function ($query) use ($filters) {

                        $query->where(
                            'unit_id',
                            $filters['unit_id']
                        );

                    }
                );


                $movements =
                    $movementQuery
                        ->orderBy('transaction_date')
                        ->orderBy('id')
                        ->get();

                /*
                |--------------------------------------------------------------------------
                | Get Movements
                |--------------------------------------------------------------------------
                */

                $movements =
                    $movementQuery
                        ->orderBy('transaction_date')
                        ->orderBy('id')
                        ->get();
            }
        /** end historical */
        /*
|--------------------------------------------------------------------------
| Get Stocks
|--------------------------------------------------------------------------
*/

if (! empty($filters['date_to'])) {

    /*
    |--------------------------------------------------------------------------
    | Historical Balance
    |--------------------------------------------------------------------------
    */

    $movementQuery =
        InventoryMovement::query()
            ->with([
                'variant.product',
                'variant.units' => function ($query) {

                    $query
                        ->active()
                        ->with('unit')
                        ->orderBy('sort_order');

                },
                'unit',
                'branch',
                'warehouse',
            ])
            ->whereDate(
                'transaction_date',
                '<=',
                $filters['date_to']
            );


    /*
    |--------------------------------------------------------------------------
    | Branch
    |--------------------------------------------------------------------------
    */

    $movementQuery->when(
        ! empty($filters['branch_id']),
        function ($query) use ($filters) {

            $query->where(
                'branch_id',
                $filters['branch_id']
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Warehouse
    |--------------------------------------------------------------------------
    */

    $movementQuery->when(
        ! empty($filters['warehouse_id']),
        function ($query) use ($filters) {

            $query->where(
                'warehouse_id',
                $filters['warehouse_id']
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Product Variant
    |--------------------------------------------------------------------------
    */

    $movementQuery->when(
        ! empty($filters['product_variant_id']),
        function ($query) use ($filters) {

            $query->where(
                'product_variant_id',
                $filters['product_variant_id']
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Unit
    |--------------------------------------------------------------------------
    */

    $movementQuery->when(
        ! empty($filters['unit_id']),
        function ($query) use ($filters) {

            $query->where(
                'unit_id',
                $filters['unit_id']
            );

        }
    );


    $movements =
        $movementQuery
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();


    /*
    |--------------------------------------------------------------------------
    | Convert Movement → Stock Balance
    |--------------------------------------------------------------------------
    */

    $stocks =
        $movements
            ->groupBy(
                function ($movement) {

                    return implode(
                        '-',
                        [
                            $movement->product_variant_id,
                            $movement->branch_id,
                            $movement->warehouse_id,
                            $movement->unit_id,
                        ]
                    );

                }
            )
            ->map(
                function ($movements) use ($filters) {

                    $first =
                        $movements->first();


                    $qtyIn =
                        $movements->sum(
                            fn ($movement) =>
                                (float)
                                $movement->qty_in
                        );


                    $qtyOut =
                        $movements->sum(
                            fn ($movement) =>
                                (float)
                                $movement->qty_out
                        );


                    $onHand =
                        $qtyIn - $qtyOut;


                    /*
                    |--------------------------------------------------------------------------
                    | Reserved
                    |--------------------------------------------------------------------------
                    |
                    | Historical reservation belum direkonstruksi
                    | dari movement. Untuk sementara 0.
                    |
                    */

                    $reserved =
                        0;


                    $available =
                        $onHand;


               /*
                |--------------------------------------------------------------------------
                | Company Costing Method
                |--------------------------------------------------------------------------
                */

                $costingMethod =
                    \App\Models\MasterData\Company::query()
                        ->where(
                            'id',
                            $first->branch?->company_id
                        )
                        ->value(
                            'inventory_costing_method'
                        )
                        ?? 'WEIGHTED_AVERAGE';


                /*
                |--------------------------------------------------------------------------
                | Weighted Average / FIFO / LIFO Costing
                |--------------------------------------------------------------------------
                */

                $costing =
                    $this
                        ->inventoryCostingService
                        ->calculate(
                            productVariantId:
                                $first->product_variant_id,

                            branchId:
                                $first->branch_id,

                            warehouseId:
                                $first->warehouse_id,

                            unitId:
                                $first->unit_id,

                            method:
                                $costingMethod,

                            dateTo:
                                $filters['date_to']
                        );


                    $stockValue =
                        (float)
                        (
                            $costing['stock_value']
                            ?? 0
                        );


                    $averageCost =
                        (float)
                        (
                            $costing['average_cost']
                            ?? 0
                        );
                    /*
                    |--------------------------------------------------------------------------
                    | Create Stock-like Object
                    |--------------------------------------------------------------------------
                    */

                    $stock =
                        clone $first;

                    $stock->on_hand_qty =
                        $onHand;

                    $stock->reserved_qty =
                        $reserved;

                    $stock->available_qty =
                        $available;

                    $stock->average_cost =
                        $averageCost;


                    return $stock;

                }
            )
            ->filter(
                fn ($stock) =>
                    (float)
                    $stock->on_hand_qty > 0
            )
            ->values();

} else {

    /*
    |--------------------------------------------------------------------------
    | Current Stock
    |--------------------------------------------------------------------------
    */

     $stocks =
        $query
            ->get();

        }
        /*
        |--------------------------------------------------------------------------
        | Retail Price Type
        |--------------------------------------------------------------------------
        */

        $retailPriceTypeId =
            PriceType::query()
                ->where(
                    'code',
                    'RETAIL'
                )
                ->where(
                    'is_active',
                    true
                )
                ->value('id');

/*
|--------------------------------------------------------------------------
| Group
|--------------------------------------------------------------------------
*/

$rows =
    $stocks
        ->groupBy(
            function ($stock) {

                return implode(
                    '-',
                    [
                        $stock->product_variant_id,
                        $stock->branch_id,
                        $stock->warehouse_id,
                    ]
                );

            }
        )
        ->map(
            function ($stocks) use (
                $retailPriceTypeId
            ) {

                $first =
                    $stocks->first();


                /*
                |--------------------------------------------------------------------------
                | Stock Totals
                |--------------------------------------------------------------------------
                */

                $onHand =
                    $stocks->sum(
                        fn ($stock) =>
                            (float)
                            $stock->on_hand_qty
                    );


                $reserved =
                    $stocks->sum(
                        fn ($stock) =>
                            (float)
                            $stock->reserved_qty
                    );


                $available =
                    $stocks->sum(
                        fn ($stock) =>
                            (float)
                            $stock->available_qty
                    );


                /*
                |--------------------------------------------------------------------------
                | Stock Value
                |--------------------------------------------------------------------------
                */

                $stockValue =
                    $stocks->sum(
                        fn ($stock) =>
                            (
                                (float)
                                $stock->on_hand_qty
                            )
                            *
                            (
                                (float)
                                $stock->average_cost
                            )
                    );


                /*
                |--------------------------------------------------------------------------
                | Average Cost
                |--------------------------------------------------------------------------
                */

                $averageCost =
                    $onHand > 0
                        ? $stockValue / $onHand
                        : 0;


                /*
                |--------------------------------------------------------------------------
                | Base Unit
                |--------------------------------------------------------------------------
                */

                $baseUnit =
                    $first
                        ->variant
                        ?->units
                        ?->firstWhere(
                            'is_base',
                            true
                        );


                $baseUnitId =
                    $baseUnit?->unit_id;


                /*
                |--------------------------------------------------------------------------
                | Sales Quantity - Base Unit
                |--------------------------------------------------------------------------
                */

                $salesQty =
                    $stocks->sum(
                        function ($stock) use ($first) {

                            $variantUnit =
                                $first
                                    ->variant
                                    ?->units
                                    ?->firstWhere(
                                        'unit_id',
                                        $stock->unit_id
                                    );


                            $conversionFactor =
                                (float) (
                                    $variantUnit
                                        ?->conversion_factor
                                    ?? 1
                                );


                            return
                                (
                                    (float)
                                    $stock->on_hand_qty
                                )
                                *
                                $conversionFactor;

                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | Retail Selling Price - Base Unit
                |--------------------------------------------------------------------------
                */

                $retailPrice =
                    ProductVariantPrice::query()
                        ->where(
                            'product_variant_id',
                            $first->product_variant_id
                        )
                        ->where(
                            'unit_id',
                            $baseUnitId
                        )
                        ->where(
                            'price_type_id',
                            $retailPriceTypeId
                        )
                        ->where(
                            'is_active',
                            true
                        )
                        ->whereDate(
                            'effective_from',
                            '<=',
                            now()
                        )
                        ->where(
                            function ($query) {

                                $query
                                    ->whereNull(
                                        'effective_until'
                                    )
                                    ->orWhereDate(
                                        'effective_until',
                                        '>=',
                                        now()
                                    );

                            }
                        )
                        ->orderByDesc(
                            'effective_from'
                        )
                        ->first();


                $sellingPrice =
                    (float) (
                        $retailPrice?->selling_price
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Sales Value
                |--------------------------------------------------------------------------
                */

                $salesValue =
                    $salesQty
                    *
                    $sellingPrice;


                /*
                |--------------------------------------------------------------------------
                | Unit Details
                |--------------------------------------------------------------------------
                */

                $units =
                    $stocks
                        ->map(
                            function ($stock) {

                                return [

                                    'id' =>
                                        $stock->id,

                                    'unit_id' =>
                                        $stock->unit_id,

                                    'unit_name' =>
                                        $stock->unit?->name,

                                    'on_hand_qty' =>
                                        (float)
                                        $stock->on_hand_qty,

                                    'reserved_qty' =>
                                        (float)
                                        $stock->reserved_qty,

                                    'available_qty' =>
                                        (float)
                                        $stock->available_qty,

                                    'average_cost' =>
                                        (float)
                                        $stock->average_cost,

                                    'stock_value' =>
                                        (
                                            (float)
                                            $stock->on_hand_qty
                                        )
                                        *
                                        (
                                            (float)
                                            $stock->average_cost
                                        ),

                                ];

                            }
                        )
                        ->values();


                /*
                |--------------------------------------------------------------------------
                | Row
                |--------------------------------------------------------------------------
                */

                return [

                    'id' =>
                        $first->id,

                    'product_variant_id' =>
                        $first->product_variant_id,


                    'product' => [

                        'id' =>
                            $first
                                ->variant
                                ?->product
                                ?->id,

                        'name' =>
                            $first
                                ->variant
                                ?->product
                                ?->name,

                    ],


                    'variant' => [

                        'id' =>
                            $first
                                ->variant
                                ?->id,

                        'sku' =>
                            $first
                                ->variant
                                ?->sku,

                        'name' =>
                            $first
                                ->variant
                                ?->name,

                    ],


                    'branch' => [

                        'id' =>
                            $first
                                ->branch
                                ?->id,

                        'name' =>
                            $first
                                ->branch
                                ?->name,

                    ],


                    'warehouse' => [

                        'id' =>
                            $first
                                ->warehouse
                                ?->id,

                        'name' =>
                            $first
                                ->warehouse
                                ?->name,

                    ],


                    'units' =>
                        $units,

                    'unit_count' =>
                        $units->count(),


                    'on_hand_qty' =>
                        $onHand,

                    'reserved_qty' =>
                        $reserved,

                    'available_qty' =>
                        $available,

                    'average_cost' =>
                        $averageCost,

                    'stock_value' =>
                        $stockValue,


                    /*
                    |--------------------------------------------------------------------------
                    | Sales
                    |--------------------------------------------------------------------------
                    */

                    'sales_qty' =>
                        $salesQty,

                    'selling_price' =>
                        $sellingPrice,

                    'sales_value' =>
                        $salesValue,

                ];

            }
        )
        ->values();

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $sortBy =
            $filters['sort_by']
            ?? 'id';

        $sortDirection =
            $filters['sort_direction']
            ?? 'desc';


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


        $allowedSorts = [

            'id',

            'on_hand_qty',

            'reserved_qty',

            'available_qty',

            'stock_value',

            'stock_value',

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


        $rows =
            $rows
                ->sortBy(
                    function ($row) use ($sortBy) {

                        return $row[$sortBy]
                            ?? 0;

                    },
                    SORT_REGULAR,
                    $sortDirection === 'desc'
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statistics = [

            'total_products' =>
                $rows->count(),

            'total_on_hand' =>
                $rows->sum(
                    'on_hand_qty'
                ),

            'total_reserved' =>
                $rows->sum(
                    'reserved_qty'
                ),

            'total_available' =>
                $rows->sum(
                    'available_qty'
                ),

            'total_stock_value' =>
                $rows->sum(
                    'stock_value'
                ),

            'total_sales_value' =>
            $rows->sum(
                'sales_value'
            ),

        ];


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage =
            max(
                1,
                (int) (
                    $filters['per_page']
                    ?? 10
                )
            );

        $page =
            max(
                1,
                (int) (
                    $filters['page']
                    ?? 1
                )
            );


        $total =
            $rows->count();


        $items =
            $rows
                ->slice(
                    (
                        $page - 1
                    )
                    *
                    $perPage,
                    $perPage
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Paginator
        |--------------------------------------------------------------------------
        */

        $paginator =
            new \Illuminate\Pagination\LengthAwarePaginator(

                $items,

                $total,

                $perPage,

                $page,

                [
                    'path' =>
                        request()->url(),

                    'query' =>
                        request()->query(),

                ]

            );


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return $paginator;
    }
/*
|--------------------------------------------------------------------------
| Inventory Movements
|--------------------------------------------------------------------------
*/

public function getMovements(
    int $productVariantId,
    int $branchId,
    int $warehouseId,
    ?int $unitId = null
) {

    return InventoryMovement::query()
        ->with([
            'unit',
        ])
        ->where(
            'product_variant_id',
            $productVariantId
        )
        ->where(
            'branch_id',
            $branchId
        )
        ->where(
            'warehouse_id',
            $warehouseId
        )
        ->when(
            $unitId,
            function ($query) use ($unitId) {

                $query->where(
                    'unit_id',
                    $unitId
                );

            }
        )
        ->orderByDesc(
            'transaction_date'
        )
        ->orderByDesc(
            'id'
        )
        ->get()
        ->map(
            function ($movement) {

                return [

                    'id' =>
                        $movement->id,

                    'date' =>
                        $movement->transaction_date
                            ?->format('Y-m-d'),

                    'reference_type' =>
                        $movement->reference_type,

                    'reference_number' =>
                        $movement->reference_number,

                    'qty_in' =>
                        (float)
                        $movement->qty_in,

                    'qty_out' =>
                        (float)
                        $movement->qty_out,

                    'unit_id' =>
                        $movement->unit_id,

                    'unit_name' =>
                        $movement->unit?->name,

                    'unit_cost' =>
                        (float)
                        $movement->unit_cost,

                    'total_cost' =>
                        (float)
                        $movement->total_cost,

                    'description' =>
                        $movement->description,

                ];

            }
        )
        ->values();

}

    /*
    |--------------------------------------------------------------------------
    | Unit Details
    |--------------------------------------------------------------------------
    */

    public function getUnitDetails(
        int $productVariantId,
        int $branchId,
        int $warehouseId
    ) {

        return ProductStock::query()
            ->with([
                'unit',
                'variant.product',
                'branch',
                'warehouse',
            ])
            ->where(
                'product_variant_id',
                $productVariantId
            )
            ->where(
                'branch_id',
                $branchId
            )
            ->where(
                'warehouse_id',
                $warehouseId
            )
            ->orderBy(
                'unit_id'
            )
            ->get()
            ->map(
                function ($stock) {

                    return [

                        'id' =>
                            $stock->id,

                        'unit' => [

                            'id' =>
                                $stock->unit?->id,

                            'name' =>
                                $stock->unit?->name,

                        ],

                        'on_hand_qty' =>
                            (float)
                            $stock->on_hand_qty,

                        'reserved_qty' =>
                            (float)
                            $stock->reserved_qty,

                        'available_qty' =>
                            (float)
                            $stock->available_qty,

                        'average_cost' =>
                            (float)
                            $stock->average_cost,

                        'stock_value' =>
                            (
                                (float)
                                $stock->on_hand_qty
                            )
                            *
                            (
                                (float)
                                $stock->average_cost
                            ),

                    ];

                }
            )
            ->values();

    }
}