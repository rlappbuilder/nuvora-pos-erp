<?php

namespace App\Http\Controllers\Inventory;

use Inertia\Inertia;

use App\Models\StockMovement;

use App\Http\Controllers\Controller;

class StockCardController extends Controller
{
    public function index()
    {
        $movements = StockMovement::with(

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

        ->latest()

        ->paginate(20)

        ->withQueryString();

        return Inertia::render(

            'Inventory/StockCard/Index',

            [

                'movements' => $movements,

                'filters' => [

                    'search' => request(
                        'search'
                    )

                ]

            ]

        );
    }
}