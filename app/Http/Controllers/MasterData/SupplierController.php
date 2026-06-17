<?php

namespace App\Http\Controllers\MasterData;

use Inertia\Inertia;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::when(

            request('search'),

            function ($query) {

                $query->where(

                    'supplier_code',

                    'like',

                    '%' . request('search') . '%'

                )

                ->orWhere(

                    'name',

                    'like',

                    '%' . request('search') . '%'

                );

            }

        )

        ->latest()

        ->paginate(10)

        ->withQueryString();

        return Inertia::render(

            'MasterData/Suppliers/Index',

            [

                'suppliers' => $suppliers,

                'filters' => [

                    'search' => request(
                        'search'
                    )

                ]

            ]

        );
    }

    public function create()
    {
        return Inertia::render(

            'MasterData/Suppliers/Create'

        );
    }
}