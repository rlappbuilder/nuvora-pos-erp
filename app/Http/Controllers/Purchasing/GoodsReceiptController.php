<?php

namespace App\Http\Controllers\Purchasing;


use Inertia\Inertia;
use App\Models\PurchaseOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDetail;
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

                        return back();
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
    
    
        public function store(
            Request $request
        )
        {
            $request->validate([

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
                    $item['qty_received']
                    <= 0
                ) {

                    return back()
                        ->withErrors([

                            'items' =>
                            'Qty Received harus lebih besar dari 0'

                        ]);

                }

            }

            $last = GoodsReceipt::withTrashed()
                ->latest('id')
                ->first();

            $number = $last
                ? $last->id + 1
                : 1;

            $grnNumber =
                'GRN' .
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
                    'goods-receipts.index'
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

    ]);

    return Inertia::render(

        'Purchasing/GoodsReceipts/Show',

        [

            'goodsReceipt' =>

                $goodsReceipt

        ]

    );
}
}