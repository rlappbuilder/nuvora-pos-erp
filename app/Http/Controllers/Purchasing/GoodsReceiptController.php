<?php

namespace App\Http\Controllers\Purchasing;


use Inertia\Inertia;
use App\Models\PurchaseOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDetail;
use App\Models\InventoryMovement;
use App\Models\ProductStock;
class GoodsReceiptController extends Controller
{
                public function create(
                PurchaseOrder $purchaseOrder
            )
            {
                dd($purchaseOrder);
            }
                          public function index()
                                {
                                    $query = GoodsReceipt::with([

                                        'supplier',

                                        'warehouse'

                                    ]);

                                    if (
                                        request('search')
                                    ) {

                                        $query->where(

                                            'grn_number',

                                            'like',

                                            '%' .
                                            request('search')
                                            . '%'

                                        );

                                    }

                                    if (
                                        request('status')
                                    ) {

                                        $query->where(

                                            'status',

                                            request('status')

                                        );

                                    }

                                    $goodsReceipts =

                                        $query

                                        ->latest()

                                        ->paginate(10)

                                        ->withQueryString();

                                    $totalGrn =

                                        GoodsReceipt::count();

                                    $totalDraft =

                                        GoodsReceipt::where(

                                            'status',

                                            'Draft'

                                        )->count();

                                    $totalPosted =

                                        GoodsReceipt::where(

                                            'status',

                                            'Posted'

                                        )->count();

                                    $totalCancelled =

                                        GoodsReceipt::where(

                                            'status',

                                            'Cancelled'

                                        )->count();

                                    return Inertia::render(

                                        'Purchasing/GoodsReceipts/Index',

                                        [

                                            'goodsReceipts' =>

                                                $goodsReceipts,

                                            'filters' => [

                                                'search' =>

                                                    request(
                                                        'search'
                                                    ),

                                                'status' =>

                                                    request(
                                                        'status'
                                                    ),

                                            ],

                                            'totalGrn' =>

                                                $totalGrn,

                                            'totalDraft' =>

                                                $totalDraft,

                                            'totalPosted' =>

                                                $totalPosted,

                                            'totalCancelled' =>

                                                $totalCancelled,

                                        ]

                                    );
                                }

           

          public function createFromPurchaseOrder(
    PurchaseOrder $purchaseOrder
)
{
    if (

        ! in_array(

            $purchaseOrder->status,

            [

                'Approved',

                'Partially Received'

            ]

        )

    ) {

        return back()

            ->with(

                'error',

                'Purchase Order tidak dapat dibuatkan Goods Receipt.'

            );

    }

    $draftGrn = GoodsReceipt::where(

        'purchase_order_id',

        $purchaseOrder->id

    )

    ->where(

        'status',

        'Draft'

    )

    ->first();

    if (

        $draftGrn

    ) {

        return redirect()

            ->route(

                'goods-receipts.show',

                $draftGrn->id

            )

            ->with(

                'warning',

                'Masih ada Draft Goods Receipt untuk Purchase Order ini. Silakan Post atau Cancel terlebih dahulu.'

            );

    }

    $purchaseOrder->load([

        'supplier',

        'warehouse',

        'details.product',

        'goodsReceipts.details',

    ]);

    foreach (

        $purchaseOrder->details

        as $detail

    ) {

        $receivedQty = 0;

        foreach (

            $purchaseOrder->goodsReceipts

                ->where(
                    'status',
                    'Posted'
                )

            as $grn

        ) {

            $grnDetail =

                $grn->details

                    ->where(

                        'product_id',

                        $detail->product_id

                    )

                    ->first();

            if (

                $grnDetail

            ) {

                $receivedQty +=

                    $grnDetail
                        ->qty_received;

            }

        }

        $detail->received_qty =

            $receivedQty;

        $detail->remaining_qty =

            max(

                0,

                $detail->qty
                -
                $receivedQty

            );

    }

    return Inertia::render(

        'Purchasing/GoodsReceipts/Create',

        [

            'purchaseOrder' =>

                $purchaseOrder,

        ]

    );
}
    
    
       public function store(
    Request $request
)
{
    $request->validate([

        'purchase_order_id' =>

            'required|exists:purchase_orders,id',

        'supplier_id' =>

            'required|exists:suppliers,id',

        'warehouse_id' =>

            'required|exists:warehouses,id',

        'receipt_date' =>

            'required|date',

        'supplier_do_number' =>

            'required|string|max:100',

        'items' =>

            'required|array|min:1',

    ]);

    foreach (

        $request->items

        as $item

    ) {

        if (

            $item['remaining_qty']

            <= 0

        ) {

            continue;

        }

        if (

            $item['qty_received']

            <= 0

        ) {

            return back()

                ->withErrors([

                    'items' =>

                    'Qty Received harus lebih besar dari 0.'

                ]);

        }

        if (

            $item['qty_received']

            >

            $item['remaining_qty']

        ) {

            return back()

                ->withErrors([

                    'items' =>

                    'Tidak dapat Menyimpan Data : Qty melebihi sisa penerimaan.'

                ]);

        }

    }

    $last = GoodsReceipt::withTrashed()

        ->latest('id')

        ->first();

    $number =

        $last

        ? $last->id + 1

        : 1;

    $grnNumber =

        'GRN'

        .

        str_pad(

            $number,

            5,

            '0',

            STR_PAD_LEFT

        );

    $goodsReceipt =

        GoodsReceipt::create([

            'grn_number' =>

                $grnNumber,

            'purchase_order_id' =>

                $request->purchase_order_id,

            'supplier_id' =>

                $request->supplier_id,

            'warehouse_id' =>

                $request->warehouse_id,

            'receipt_date' =>

                $request->receipt_date,

            'supplier_do_number' =>

                $request->supplier_do_number,

            'remarks' =>

                $request->remarks,

            'status' =>

                'Draft',

            'created_by' =>

                auth()->id(),

        ]);

    foreach (

        $request->items

        as $item

    ) {

        if (

            $item['qty_received']

            <= 0

        ) {

            continue;

        }

        GoodsReceiptDetail::create([

            'goods_receipt_id' =>

                $goodsReceipt->id,

            'product_id' =>

                $item['product_id'],

            'qty_received' =>

                $item['qty_received'],

            'unit_cost' =>

                $item['unit_cost'],

            'line_total' =>

                $item['qty_received']

                *

                $item['unit_cost'],

        ]);

    }

    return redirect()

        ->route(

            'goods-receipts.show',

            $goodsReceipt->id

        )

        ->with(

            'success',

            'Goods Receipt berhasil dibuat.'

        );
}

        public function show(
    GoodsReceipt $goodsReceipt
)
{

   $goodsReceipt->load([

    'supplier',

    'warehouse',

    'purchaseOrder',

    'details.product',

    'creator',

    'poster',

    'canceller',

]);

    return Inertia::render(

        'Purchasing/GoodsReceipts/Show',

        [

            'goodsReceipt' =>

                $goodsReceipt

        ]

    );
}
public function post(
   
GoodsReceipt $goodsReceipt
)

{
if ( 
$goodsReceipt->status
!== 'Draft'
) {
   


    return back();

}

$goodsReceipt->update([

    'status' => 'Posted',

    'posted_at' => now(),

    'posted_by' => auth()->id(),

]);

$goodsReceipt->load('details');

foreach (

    $goodsReceipt->details

    as $detail

) {

    $lastMovement =

        InventoryMovement::where(
            'product_id',
            $detail->product_id
        )
        ->where(
            'warehouse_id',
            $goodsReceipt->warehouse_id
        )
        ->latest('id')
        ->first();

    $currentBalance =

        $lastMovement
            ? $lastMovement->balance_qty
            : 0;

    InventoryMovement::create([

        'product_id' =>
            $detail->product_id,

        'warehouse_id' =>
            $goodsReceipt->warehouse_id,

        'reference_type' =>
            'GRN',

        'reference_id' =>
            $goodsReceipt->id,

        'reference_number' =>
            $goodsReceipt->grn_number,

        'qty_in' =>
            $detail->qty_received,

        'qty_out' =>
            0,

        'balance_qty' =>
            $currentBalance +
            $detail->qty_received,

        'transaction_date' =>
            now(),

        'created_by' =>
            auth()->id(),

    ]);
                $stock = ProductStock::firstOrNew([

                'product_id' =>
                    $detail->product_id,

                'warehouse_id' =>
                    $goodsReceipt->warehouse_id,

            ]);

            $stock->qty = (

                $stock->exists
                    ? $stock->qty
                    : 0

            ) + $detail->qty_received;

            $stock->created_by ??=
                auth()->id();

            $stock->updated_by =
                auth()->id();

            $stock->save();

}


$purchaseOrder =

    $goodsReceipt
        ->purchaseOrder;

$totalOrderedQty =

    $purchaseOrder
        ->details
        ->sum('qty');

$totalReceivedQty =

    GoodsReceipt::where(

        'purchase_order_id',

        $purchaseOrder->id

    )
    ->where(

        'status',

        'Posted'

    )
    ->with('details')
    ->get()
    ->sum(function (
        $grn
    ) {

        return $grn
            ->details
            ->sum(
                'qty_received'
            );

    });

if (

    $totalReceivedQty

    >=

    $totalOrderedQty

) {

    $purchaseOrder->update([

        'status' =>

            'Fully Received'

    ]);

} else {

    $purchaseOrder->update([

        'status' =>

            'Partially Received'

    ]);

}

return back()

    ->with(

        'success',

        'Goods Receipt posted successfully.'

    );


}

public function cancel(
    Request $request,
    GoodsReceipt $goodsReceipt
)
{
    $request->validate([

        'cancel_reason' =>

            'required|string|max:500'

    ]);

    $goodsReceipt->update([

        'status' =>

            'Cancelled',

        'cancel_reason' =>

            $request->cancel_reason,

        'cancelled_at' =>

            now(),

        'cancelled_by' =>

            auth()->id(),

    ]);

    return back()->with(

        'success',

        'Goods Receipt berhasil dibatalkan.'

    );
}

}