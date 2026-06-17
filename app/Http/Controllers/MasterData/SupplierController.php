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

    public function store(
    Request $request
)
{
    $validated = $request->validate([

        

        'name' => [
            'required',
            'max:255'
        ],

        'contact_person' => [
            'nullable',
            'max:255'
        ],

        'phone' => [
            'nullable',
            'max:50'
        ],

        'email' => [
            'nullable',
            'email'
        ],

        'city' => [
            'nullable',
            'max:100'
        ],

        'tax_number' => [
            'nullable',
            'max:100'
        ],

        'payment_term' => [
            'nullable',
            'integer'
        ],

        'address' => [
            'nullable'
        ],

        'status' => [
            'boolean'
        ],

    ]);

   

$lastSupplier = Supplier::latest(
        'id'
    )->first();

    $nextNumber = $lastSupplier
        ? $lastSupplier->id + 1
        : 1;

    $validated['supplier_code'] =
        'SUP' .
        str_pad(
            $nextNumber,
            5,
            '0',
            STR_PAD_LEFT
        );
 $validated['created_by'] =
        auth()->id();
    Supplier::create(
        $validated
    );

    return redirect()

        ->route(
            'suppliers.index'
        )

        ->with(

            'success',

            'Supplier created successfully.'

        );
}
public function destroy(
    Supplier $supplier
)
{
    $supplier->delete();

    return redirect()

        ->route(
            'suppliers.index'
        )

        ->with(

            'success',

            'Supplier deleted successfully.'

        );
}
}