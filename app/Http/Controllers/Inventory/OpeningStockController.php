<?php

namespace App\Http\Controllers\Inventory;

use Inertia\Inertia;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
class OpeningStockController extends Controller
{
   public function create()
{
    return Inertia::render(

        'Inventory/OpeningStock/Create',

        [

            'products' => Product::with(

                'category',
                'brand'

            )

            ->where(

                'status',
                true

            )

            ->get(),

            'warehouses' => Warehouse::where(

                'status',
                true

            )

            ->get(),

        ]

    );
}

    public function store(
    Request $request
) {

    $request->validate([

        'warehouse_id' => [
            'required',
            'exists:warehouses,id'
        ],

        'product_id' => [
            'required',
            'exists:products,id'
        ],

        'qty' => [
            'required',
            'numeric',
            'min:0.01'
        ],

        'unit_cost' => [
            'required',
            'numeric',
            'min:0'
        ],

        'remarks' => [
            'nullable',
            'string'
        ],

    ]);

    DB::transaction(function () use ($request) {

        $stock = ProductStock::firstOrNew([

            'product_id' => $request->product_id,

            'warehouse_id' => $request->warehouse_id,

        ]);

        $stock->qty = (

            $stock->exists

                ? $stock->qty

                : 0

        ) + $request->qty;

        $stock->created_by ??= auth()->id();

        $stock->updated_by = auth()->id();

        $stock->save();

        StockMovement::create([

            'product_id' => $request->product_id,

            'warehouse_id' => $request->warehouse_id,

            'transaction_type' => 'OPENING',

            'reference_no' => null,

            'qty' => $request->qty,

            'unit_cost' => $request->unit_cost,

            'remarks' => $request->remarks,

            'created_by' => auth()->id(),

        ]);

    });

    return redirect()

        ->back()

        ->with(

            'success',

            'Opening stock saved successfully.'

        );

}
}