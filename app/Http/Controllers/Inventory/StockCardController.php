<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Services\Inventory\StockCardService;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockCardController extends Controller
{
    protected StockCardService $stockCardService;

    public function __construct(
        StockCardService $stockCardService
    ) {
        $this->stockCardService =
            $stockCardService;
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

        $filters = [

            'product_variant_id' =>
                $request->input(
                    'product_variant_id'
                ),

            'branch_id' =>
                $request->input(
                    'branch_id'
                ),

            'warehouse_id' =>
                $request->input(
                    'warehouse_id'
                ),

            'unit_id' =>
                $request->input(
                    'unit_id'
                ),

            'date_from' =>
                $request->input(
                    'date_from'
                ),

            'date_to' =>
                $request->input(
                    'date_to'
                ),
            'per_page' =>
                $request->input(
                    'per_page',
                    25
                ),

            'page' =>
                $request->input(
                    'page',
                    1
                ),

            'sort_by' =>
                $request->input(
                    'sort_by',
                    'date'
                ),

            'sort_direction' =>
                $request->input(
                    'sort_direction',
                    'desc'
                ),
        ];


        /*
        |--------------------------------------------------------------------------
        | Stock Card
        |--------------------------------------------------------------------------
        |
        | Jangan query kalau filter utama belum lengkap.
        |
        */

        $stockCard =
            $this->stockCardService
                ->getStockCard(

                    (int)
                    (
                        $filters['product_variant_id']
                        ?? 0
                    ),

                    (int)
                    (
                        $filters['branch_id']
                        ?? 0
                    ),

                    (int)
                    (
                        $filters['warehouse_id']
                        ?? 0
                    ),

                    (int)
                    (
                        $filters['unit_id']
                        ?? 0
                    ),

                    $filters['date_from']
                    ?? null,

                    $filters['date_to']
                    ?? null,

                    $filters['sort_by']
                    ?? 'date',

                    $filters['sort_direction']
                    ?? 'desc',

                    (int)
                    (
                        $request->input(
                            'per_page'
                        )
                        ?? 25
                    ),

                    (int)
                    (
                        $request->input(
                            'page'
                        )
                        ?? 1
                    )

                );
        /*
        |--------------------------------------------------------------------------
        | Branches
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
        | Warehouses
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
        | Product Variants
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
        | Units
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
        | Return
        |--------------------------------------------------------------------------
        */

        return Inertia::render(

            'Inventory/StockCard/Index',

            [

                'title' =>
                    'Stock Card',

                'stockCard' =>
                    $stockCard,

                'branches' =>
                    $branches,

                'warehouses' =>
                    $warehouses,

                'variants' =>
                    $variants,

                'units' =>
                    $units,

                'filters' =>
                    $filters,

            ]

        );

    }
}