<?php

namespace App\Http\Controllers\Inventory;

use Inertia\Inertia;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Warehouse;
use App\Http\Controllers\Controller;

class StockCardController extends Controller
{
    public function index()
    {
       $movements = InventoryMovement::with(

    'product',
    'warehouse'

)

->when(

    request('product_id'),

    function ($query) {

        $query->where(

            'product_id',

            request('product_id')

        );

    }

)

->when(

    request('warehouse_id'),

    function ($query) {

        $query->where(

            'warehouse_id',

            request('warehouse_id')

        );

    }

)

->when(

    request('date_from'),

    function ($query) {

        $query->whereDate(

            'transaction_date',

            '>=',

            request('date_from')

        );

    }

)

->when(

    request('date_to'),

    function ($query) {

        $query->whereDate(

            'transaction_date',

            '<=',

            request('date_to')

        );

    }

)

->latest()

->paginate(20)

->withQueryString();
         return Inertia::render(

                'Inventory/StockCard/Index',

                [

                    'movements' => $movements,

                    'products' => collect([

                                [

                                        'id' => '',

                                        'name' => 'Semua Produk',

                                    ]

                                ])

                                ->merge(

                                    Product::orderBy('name')->get()

                                )

                                ->values(),

                   'warehouses' => collect([

                        [

                            'id' => '',

                            'name' => 'Semua Gudang',

                        ]

                    ])

                    ->merge(

                        Warehouse::orderBy('name')->get()

                    )

                    ->values(),

                   'filters' => [

                    'product_id' => request(
                        'product_id'
                    ),

                    'warehouse_id' => request(
                        'warehouse_id'
                    ),

                    'date_from' => request(
                        'date_from'
                    ),

                    'date_to' => request(
                        'date_to'
                    ),

                ],

                ]

            );
    }
}