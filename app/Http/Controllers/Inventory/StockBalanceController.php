<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;

use App\Services\Inventory\StockBalanceService;

class StockBalanceController extends Controller
{
    public function __construct(
        protected StockBalanceService $stockBalanceService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $filters =
        
                $request->only([
                'search',
                'date_from',
                'date_to',
                'branch_id',
                'warehouse_id',
                'product_variant_id',
                'unit_id',
                'per_page',
                'page',
                'sort_by',
                'sort_direction',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Stock Balance
        |--------------------------------------------------------------------------
        */

        $stockBalance =
            $this->stockBalanceService
                ->getStockBalance(
                    $filters
                );
       // dd($stockBalance->first());
        /*
        |--------------------------------------------------------------------------
        | Master Data - Branches
        |--------------------------------------------------------------------------
        */

        $branches =
            Branch::query()
                ->orderBy('name')
                ->get()
                ->map(
                    fn ($branch) => [

                        'id' =>
                            $branch->id,

                        'label' =>
                            $branch->name,

                    ]
                )
                ->values();

        /*
        |--------------------------------------------------------------------------
        | Master Data - Warehouses
        |--------------------------------------------------------------------------
        */

        $warehouses =
            Warehouse::query()
                ->orderBy('name')
                ->get()
                ->map(
                    fn ($warehouse) => [

                        'id' =>
                            $warehouse->id,

                        'label' =>
                            $warehouse->name,

                        'branch_id' =>
                            $warehouse->branch_id,

                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Master Data - Variants
        |--------------------------------------------------------------------------
        */

        $variants =
            ProductVariant::query()
                ->active()
                ->with('product')
                ->orderBy('sku')
                ->get([
                    'id',
                    'product_id',
                    'sku',
                    'name',
                ])
                ->map(
                    fn ($variant) => [

                        'id' =>
                            $variant->id,

                        'label' =>
                            implode(
                                ' - ',
                                array_filter([
                                    $variant
                                        ->product
                                        ?->name,

                                    $variant->name,

                                    $variant->sku,
                                ])
                            ),

                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Master Data - Units
        |--------------------------------------------------------------------------
        */

        $units =
            Unit::query()
                ->orderBy('name')
                ->get()
                ->map(
                    fn ($unit) => [

                        'id' =>
                            $unit->id,

                        'label' =>
                            $unit->name,

                    ]
                )
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        |
        | Statistics mengikuti filter yang sama.
        |
        */

        $statistics = [

            'total_products' =>
                $stockBalance
                    ->total(),

            'total_on_hand' =>
                $stockBalance
                    ->getCollection()
                    ->sum(
                        'on_hand_qty'
                    ),

            'total_reserved' =>
                $stockBalance
                    ->getCollection()
                    ->sum(
                        'reserved_qty'
                    ),

            'total_available' =>
                $stockBalance
                    ->getCollection()
                    ->sum(
                        'available_qty'
                    ),

            'total_stock_value' =>
                $stockBalance
                    ->getCollection()
                    ->sum(
                        'stock_value'
                    ),

        ];


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Inventory/StockBalance/Index',
            [

                'title' =>
                    'Stock Balance',

                'stockBalance' =>
                    $stockBalance,

                'statistics' =>
                    $statistics,

                'branches' =>
                    $branches,

                'warehouses' =>
                    $warehouses,

                'variants' =>
                    $variants,

                'units' =>
                    $units,

            'filters' =>
                $request->only([
                    'search',
                    'date_from',
                    'date_to',
                    'branch_id',
                    'warehouse_id',
                    'product_variant_id',
                    'unit_id',
                    'per_page',
                    'sort_by',
                    'sort_direction',
                ]),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Unit Details
    |--------------------------------------------------------------------------
    */

    public function data(
        Request $request
    ) {

        $validated =
            $request->validate([

                'product_variant_id' => [
                    'required',
                    'integer',
                    'exists:product_variants,id',
                ],

                'branch_id' => [
                    'required',
                    'integer',
                    'exists:branches,id',
                ],

                'warehouse_id' => [
                    'required',
                    'integer',
                    'exists:warehouses,id',
                ],

            ]);


        $details =
            $this->stockBalanceService
                ->getUnitDetails(

                    $validated[
                        'product_variant_id'
                    ],

                    $validated[
                        'branch_id'
                    ],

                    $validated[
                        'warehouse_id'
                    ]

                );


        return response()->json([

            'data' =>
                $details,

        ]);
    }
    public function movements(
    Request $request
) {

    $validated =
        $request->validate([

            'product_variant_id' =>
                ['required', 'integer'],

            'branch_id' =>
                ['required', 'integer'],

            'warehouse_id' =>
                ['required', 'integer'],

            'unit_id' =>
                ['nullable', 'integer'],

        ]);


    $movements =
        $this->stockBalanceService
            ->getMovements(

                (int)
                $validated['product_variant_id'],

                (int)
                $validated['branch_id'],

                (int)
                $validated['warehouse_id'],

                isset(
                    $validated['unit_id']
                )
                    ? (int)
                    $validated['unit_id']
                    : null

            );


    return response()->json([

        'data' =>
            $movements,

    ]);

}
}