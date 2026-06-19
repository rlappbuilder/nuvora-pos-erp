<?php

namespace App\Http\Controllers\Purchasing;

use Inertia\Inertia;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PurchaseOrderController extends Controller
{
    
    public function index()
{
    $purchaseOrders = PurchaseOrder::with(

        'supplier',

        'warehouse'

    )

    ->latest()

    ->paginate(10);

    return Inertia::render(

        'Purchasing/PurchaseOrders/Index',

        [

            'purchaseOrders' => $purchaseOrders

        ]

    );
}

    public function create()
    {
        return Inertia::render(

            'Purchasing/PurchaseOrders/Create',

            [

                'suppliers' => Supplier::where(
                    'status',
                    true
                )->get(),

                'warehouses' => Warehouse::where(
                    'status',
                    true
                )->get(),

                'products' => Product::where(
                    'status',
                    true
                )->get(),

            ]

        );
    }
    public function store(Request $request)
{
    $validated = $request->validate([

        'supplier_id' => 'required',

        'warehouse_id' => 'required',

        'order_date' => 'required|date',

        'items' => 'required|array|min:1',

    ]);

    $lastPO = PurchaseOrder::latest('id')
        ->first();

    $nextNumber = $lastPO
        ? $lastPO->id + 1
        : 1;

    $poNumber =
        'PO' .
        str_pad(
            $nextNumber,
            5,
            '0',
            STR_PAD_LEFT
        );

    $grandTotal = collect(

        $request->items

    )->sum(function ($item) {

        return

            (
                $item['qty']
                *
                $item['unit_cost']
            );

    });

    $purchaseOrder = PurchaseOrder::create([

        'po_number' => $poNumber,

        'supplier_id' => $request->supplier_id,

        'warehouse_id' => $request->warehouse_id,

        'order_date' => $request->order_date,

        'expected_date' => $request->expected_date,

        'remarks' => $request->remarks,

        'status' => 'Draft',

        'subtotal' => $grandTotal,

        'grand_total' => $grandTotal,

        'created_by' => auth()->id(),

    ]);

    foreach (

        $request->items

        as $item

    ) {

        PurchaseOrderDetail::create([

            'purchase_order_id' => $purchaseOrder->id,

            'product_id' => $item['product_id'],

            'qty' => $item['qty'],

            'unit_cost' => $item['unit_cost'],

            'line_total' =>

                $item['qty']
                *
                $item['unit_cost'],

        ]);

    }

    return redirect()

        ->route(
            'purchase-orders.index'
        )

        ->with(

            'success',

            'Purchase Order created successfully.'

        );
}
}