<?php

namespace App\Http\Controllers\Inventory;

use Inertia\Inertia;

use App\Models\ProductStock;

use App\Http\Controllers\Controller;

class StockBalanceController extends Controller
{
    public function index()
    {
        $stocks = ProductStock::with(

            'product',
            'warehouse'

        )

        ->when(

            request('search'),

            function ($query) {

                $query->whereHas(

                    'product',

                    function ($q) {

                        $q->where(

                            'name',

                            'like',

                            '%' . request('search') . '%'

                        )

                        ->orWhere(

                            'sku',

                            'like',

                            '%' . request('search') . '%'

                        );

                    }

                );

            }

        )

        ->paginate(10)

        ->withQueryString();

        return Inertia::render(

            'Inventory/StockBalance/Index',

            [

                'stocks' => $stocks,

                'filters' => [

                    'search' => request(
                        'search'
                    )

                ]

            ]

        );
    }
}