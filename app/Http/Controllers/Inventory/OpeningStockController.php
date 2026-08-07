<?php

namespace App\Http\Controllers\Inventory;

use Inertia\Inertia;
use App\Models\Product\Product;
use App\Models\MasterData\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\StockMovement;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\InventoryMovement;
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

      $lastMovement =

    InventoryMovement::where(

        'product_id',

        $request->product_id

    )

    ->where(

        'warehouse_id',

        $request->warehouse_id

    )

    ->latest('id')

    ->first();

$currentBalance =

    $lastMovement
        ? $lastMovement->balance_qty
        : 0;

            InventoryMovement::create([

                'product_id' =>

                    $request->product_id,

                'warehouse_id' =>

                    $request->warehouse_id,

                'reference_type' =>

                    'OPENING',

                'reference_id' =>

                    0,

                'reference_number' =>

                    'OPENING',

                'qty_in' =>

                    $request->qty,

                'qty_out' =>

                    0,

                'balance_qty' =>

                    $currentBalance
                    +
                    $request->qty,

                    'unit_cost' =>

                    $request->unit_cost,

                'total_cost' =>

                    $request->qty
                    *
                    $request->unit_cost,

                'transaction_date' =>

                    now(),

                'created_by' =>

                    auth()->id(),

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