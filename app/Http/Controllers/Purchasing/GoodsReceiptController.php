<?php

namespace App\Http\Controllers\Purchasing;


use Inertia\Inertia;
use App\Models\PurchaseOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GoodsReceiptController extends Controller
{
    public function create(
    PurchaseOrder $purchaseOrder
)
{
    dd($purchaseOrder);
}

public function createFromPurchaseOrder(
    PurchaseOrder $purchaseOrder
)
{
     
    if (
        $purchaseOrder->status
        !== 'Approved'
    ) {

        return back();

    }

    $purchaseOrder->load(

        'supplier',

        'warehouse',

        'details.product'

    );

    return Inertia::render(

        'Purchasing/GoodsReceipts/Create',

        [

            'purchaseOrder' =>

                $purchaseOrder

        ]

    );
}
    //
}
