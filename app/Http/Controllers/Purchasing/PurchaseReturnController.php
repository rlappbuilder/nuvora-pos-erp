<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;

use App\Http\Requests\Purchasing\PurchaseReturn\StorePurchaseReturnRequest;
use App\Http\Requests\Purchasing\PurchaseReturn\UpdatePurchaseReturnRequest;
use App\Http\Requests\Purchasing\PurchaseReturn\RejectPurchaseReturnRequest;

use App\Models\Purchasing\PurchaseReturnHeader;
use App\Models\Purchasing\PurchaseReturnDetail;
use App\Models\Purchasing\GoodsReceiptHeader;

use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Unit;

use App\Models\Product\ProductVariant;

use App\Services\Purchasing\PurchaseReturnService;
use App\Services\Core\CodeGeneratorService;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected PurchaseReturnService $purchaseReturnService,
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {

        $query =
            PurchaseReturnHeader::query()
                ->with([
                    'purchaseOrder',
                    'goodsReceipt',
                    'supplier',
                    'warehouse',
                    'details.productVariant.product',
                    'details.unit',
                    'inventoryMovements',
                ])

                /*
                |--------------------------------------------------------------------------
                | Search
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('search'),
                    function ($query) use ($request) {

                        $search =
                            $request->search;

                        $query->where(
                            function ($query) use ($search) {

                                $query
                                    ->where(
                                        'return_number',
                                        'like',
                                        "%{$search}%"
                                    )

                                    ->orWhereHas(
                                        'purchaseOrder',
                                        function ($purchaseOrder) use ($search) {

                                            $purchaseOrder->where(
                                                'number',
                                                'like',
                                                "%{$search}%"
                                            );

                                        }
                                    )

                                    ->orWhereHas(
                                        'goodsReceipt',
                                        function ($goodsReceipt) use ($search) {

                                            $goodsReceipt->where(
                                                'grn_number',
                                                'like',
                                                "%{$search}%"
                                            );

                                        }
                                    )

                                    ->orWhereHas(
                                        'supplier',
                                        function ($supplier) use ($search) {

                                            $supplier
                                                ->where(
                                                    'name',
                                                    'like',
                                                    "%{$search}%"
                                                )

                                                ->orWhere(
                                                    'supplier_code',
                                                    'like',
                                                    "%{$search}%"
                                                );

                                        }
                                    )

                                    ->orWhereHas(
                                        'warehouse',
                                        function ($warehouse) use ($search) {

                                            $warehouse->where(
                                                'name',
                                                'like',
                                                "%{$search}%"
                                            );

                                        }
                                    )

                                    ->orWhereHas(
                                        'details.productVariant',
                                        function ($variant) use ($search) {

                                            $variant
                                                ->where(
                                                    'sku',
                                                    'like',
                                                    "%{$search}%"
                                                )

                                                ->orWhere(
                                                    'name',
                                                    'like',
                                                    "%{$search}%"
                                                )

                                                ->orWhereHas(
                                                    'product',
                                                    function ($product) use ($search) {

                                                        $product->where(
                                                            'name',
                                                            'like',
                                                            "%{$search}%"
                                                        );

                                                    }
                                                );

                                        }
                                    );

                            }
                        );

                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Supplier Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('supplier_id'),
                    function ($query) use ($request) {

                        $query->where(
                            'supplier_id',
                            $request->supplier_id
                        );

                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Warehouse Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('warehouse_id'),
                    function ($query) use ($request) {

                        $query->where(
                            'warehouse_id',
                            $request->warehouse_id
                        );

                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Status Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('status'),
                    function ($query) use ($request) {

                        $query->where(
                            'status',
                            $request->status
                        );

                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Return Date Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('date_from'),
                    function ($query) use ($request) {

                        $query->whereDate(
                            'return_date',
                            '>=',
                            $request->date_from
                        );

                    }
                )

                ->when(
                    $request->filled('date_to'),
                    function ($query) use ($request) {

                        $query->whereDate(
                            'return_date',
                            '<=',
                            $request->date_to
                        );

                    }
                );


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $purchaseReturns =
            $query
                ->latest()
                ->paginate(
                    $request->integer(
                        'per_page',
                        10
                    )
                )
                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Row Statistics
        |--------------------------------------------------------------------------
        */

        $purchaseReturns
            ->getCollection()
            ->transform(
                function ($purchaseReturn) {

                    $purchaseReturn->total_items =
                        $purchaseReturn
                            ->details
                            ->count();

                    $purchaseReturn->total_returned =
                        $purchaseReturn
                            ->details
                            ->sum(
                                fn ($detail) =>
                                    (float)
                                    $detail->returned_qty
                            );

                    $purchaseReturn->total_cost =
                        $purchaseReturn
                            ->details
                            ->sum(
                                fn ($detail) =>
                                    (float)
                                    $detail->total_cost
                            );

                    return $purchaseReturn;

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statisticsQuery =
            clone $query;


        $statistics = [

            'total' =>
                (clone $statisticsQuery)
                    ->count(),

            'draft' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Draft'
                    )
                    ->count(),

            'submitted' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Submitted'
                    )
                    ->count(),

            'approved' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Approved'
                    )
                    ->count(),

            'rejected' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Rejected'
                    )
                    ->count(),

            'posted' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Posted'
                    )
                    ->count(),

            'cancelled' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Cancelled'
                    )
                    ->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Purchasing/PurchaseReturn/Index',

            [

                'title' =>
                    'Purchase Return',

                'purchaseReturns' =>
                    $purchaseReturns,

                'statistics' =>
                    $statistics,

                'previewNumber' =>
                    $this
                        ->codeGeneratorService
                        ->preview(
                            'purchase_return'
                        ),

                'filters' =>
                    $request->only([

                        'search',

                        'supplier_id',

                        'warehouse_id',

                        'status',

                        'per_page',

                        'date_from',

                        'date_to',

                    ]),

                ...$this->formData(),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    private function formData(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Warehouses
            |--------------------------------------------------------------------------
            */

            'warehouses' =>
                Warehouse::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'branch_id',
                        'name',
                    ])
                    ->map(
                        fn ($warehouse) => [

                            'id' =>
                                $warehouse->id,

                            'branch_id' =>
                                $warehouse->branch_id,

                            'label' =>
                                $warehouse->name,

                        ]
                    )
                    ->values(),


            /*
            |--------------------------------------------------------------------------
            | Suppliers
            |--------------------------------------------------------------------------
            */

            'suppliers' =>
                Supplier::query()
                    ->where(
                        'status',
                        true
                    )
                    ->orderBy('name')
                    ->get([
                        'id',
                        'supplier_code',
                        'name',
                    ])
                    ->map(
                        fn ($supplier) => [

                            'id' =>
                                $supplier->id,

                            'code' =>
                                $supplier->supplier_code,

                            'label' =>
                                implode(
                                    ' - ',
                                    array_filter([
                                        $supplier->supplier_code,
                                        $supplier->name,
                                    ])
                                ),

                        ]
                    )
                    ->values(),

/*
|--------------------------------------------------------------------------
| Goods Receipts
|--------------------------------------------------------------------------
|
| Hanya GRN Posted yang dapat menjadi
| sumber Purchase Return.
|
*/

'goodsReceipts' =>
    GoodsReceiptHeader::query()
        ->where(
            'status',
            'Posted'
        )
        ->with([
            'supplier',
            'warehouse',
            'purchaseOrder',
            'details.productVariant.product',
            'details.unit',
            'details.purchaseOrderDetail',
        ])
        ->orderByDesc(
            'receipt_date'
        )
        ->orderByDesc(
            'id'
        )
        ->get()
        ->map(
            function ($goodsReceipt) {

                return [

                    'id' =>
                        $goodsReceipt->id,

                    'number' =>
                        $goodsReceipt->grn_number,

                    'label' =>
                        $goodsReceipt->grn_number,

                    'receipt_date' =>
                        $goodsReceipt->receipt_date,

                    'company_id' =>
                        $goodsReceipt->company_id,

                    'branch_id' =>
                        $goodsReceipt->branch_id,

                    'warehouse_id' =>
                        $goodsReceipt->warehouse_id,

                    'supplier_id' =>
                        $goodsReceipt->supplier_id,

                    'purchase_order_id' =>
                        $goodsReceipt->purchase_order_id,

                    'supplier' => [

                        'id' =>
                            $goodsReceipt
                                ->supplier
                                ?->id,

                        'code' =>
                            $goodsReceipt
                                ->supplier
                                ?->supplier_code,

                        'name' =>
                            $goodsReceipt
                                ->supplier
                                ?->name,

                    ],

                    'warehouse' => [

                        'id' =>
                            $goodsReceipt
                                ->warehouse
                                ?->id,

                        'name' =>
                            $goodsReceipt
                                ->warehouse
                                ?->name,

                    ],

                    'purchase_order' => [

                        'id' =>
                            $goodsReceipt
                                ->purchaseOrder
                                ?->id,

                        'number' =>
                            $goodsReceipt
                                ->purchaseOrder
                                ?->number,

                    ],

                    'details' =>
                        $goodsReceipt
                            ->details
                            ->map(
                                function ($detail) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Already Returned
                                    |--------------------------------------------------------------------------
                                    */

                                    $alreadyReturnedQty =
                                        (float)
                                        PurchaseReturnDetail::query()
                                            ->where(
                                                'goods_receipt_detail_id',
                                                $detail->id
                                            )
                                            ->whereHas(
                                                'purchaseReturn',
                                                function ($query) {

                                                    $query->whereNotIn(
                                                        'status',
                                                        [
                                                            'Cancelled',
                                                            'Rejected',
                                                        ]
                                                    );

                                                }
                                            )
                                            ->sum(
                                                'returned_qty'
                                            );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Remaining Returnable
                                    |--------------------------------------------------------------------------
                                    */

                                    $receivedQty =
                                        (float)
                                        $detail->received_qty;


                                    $remainingReturnableQty =
                                        max(
                                            0,
                                            $receivedQty
                                            -
                                            $alreadyReturnedQty
                                        );


                                    return [

                                        'id' =>
                                            $detail->id,

                                        'purchase_order_detail_id' =>
                                            $detail
                                                ->purchase_order_detail_id,

                                        'product_variant_id' =>
                                            $detail
                                                ->product_variant_id,

                                        'unit_id' =>
                                            $detail
                                                ->unit_id,

                                        'ordered_qty' =>
                                            $detail
                                                ->ordered_qty,

                                        'received_qty' =>
                                            $receivedQty,

                                        'rejected_qty' =>
                                            $detail
                                                ->rejected_qty,

                                        'already_returned_qty' =>
                                            $alreadyReturnedQty,

                                        'remaining_returnable_qty' =>
                                            $remainingReturnableQty,

                                        'unit_cost' =>
                                            $detail
                                                ->unit_cost,

                                        'product' => [

                                            'id' =>
                                                $detail
                                                    ->productVariant
                                                    ?->product_id,

                                            'name' =>
                                                $detail
                                                    ->productVariant
                                                    ?->product
                                                    ?->name,

                                        ],

                                        'variant' => [

                                            'id' =>
                                                $detail
                                                    ->productVariant
                                                    ?->id,

                                            'sku' =>
                                                $detail
                                                    ->productVariant
                                                    ?->sku,

                                            'name' =>
                                                $detail
                                                    ->productVariant
                                                    ?->name,

                                        ],

                                        'unit' => [

                                            'id' =>
                                                $detail
                                                    ->unit
                                                    ?->id,

                                            'name' =>
                                                $detail
                                                    ->unit
                                                    ?->name,

                                        ],

                                    ];

                                }
                            )
                            ->filter(
                                fn ($detail) =>
                                    $detail[
                                        'remaining_returnable_qty'
                                    ] > 0
                            )
                            ->values(),

                ];

            }
        )
        ->filter(
            fn ($goodsReceipt) =>
                $goodsReceipt['details']->isNotEmpty()
        )
        ->values(),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return Inertia::render(
            'Purchasing/PurchaseReturns/Create',

            [

                'title' =>
                    'Create Purchase Return',

                'previewNumber' =>
                    $this
                        ->codeGeneratorService
                        ->preview(
                            'purchase_return'
                        ),

                ...$this->formData(),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        PurchaseReturnHeader $purchaseReturn
    ) {

        abort_if(
            ! in_array(
                $purchaseReturn->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected purchase return can be edited.'
        );


        $purchaseReturn->load([

            'purchaseOrder',

            'goodsReceipt',

            'supplier',

            'warehouse',

            'details.productVariant.product',

            'details.unit',

            'details.goodsReceiptDetail',

            'details.purchaseOrderDetail',

        ]);


        return Inertia::render(
            'Purchasing/PurchaseReturn/Edit',

            [

                'title' =>
                    'Edit Purchase Return',

                'purchaseReturn' =>
                    $purchaseReturn,

                ...$this->formData(),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StorePurchaseReturnRequest $request
    ) {

        $data =
            $request->validated();


        $this
            ->purchaseReturnService
            ->createPurchaseReturn(
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase return created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        PurchaseReturnHeader $purchaseReturn
    ) {

        $purchaseReturn->load([

            'purchaseOrder',

            'goodsReceipt',

            'supplier',

            'warehouse',

            'creator',

            'updater',

            'poster',

            'canceller',

            'details.productVariant.product',

            'details.unit',

            'details.goodsReceiptDetail',

            'details.purchaseOrderDetail',

            'activities.performer',

            'inventoryMovements',

            'inventoryMovements.productVariant.product',

            'inventoryMovements.unit',

        ]);


        return Inertia::render(
            'Purchasing/PurchaseReturns/Show',

            [

                'purchaseReturn' =>
                    $purchaseReturn,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

   public function showData(
    PurchaseReturnHeader $purchaseReturn
) {

    $purchaseReturn->load([

        'purchaseOrder',

        'goodsReceipt',

        'supplier',

        'warehouse',

        'creator',

        'updater',

        'poster',

        'canceller',

        'details.productVariant.product',

        'details.unit',

        'details.goodsReceiptDetail',

        'details.purchaseOrderDetail',

        'activities.performer',

        'inventoryMovements',

        'inventoryMovements.productVariant.product',

        'inventoryMovements.unit',

    ]);


    /*
    |--------------------------------------------------------------------------
    | Prepare Purchase Return Details
    |--------------------------------------------------------------------------
    */

    $purchaseReturn
        ->details
        ->transform(
            function ($detail) use ($purchaseReturn) {

                /*
                |--------------------------------------------------------------------------
                | Ordered Quantity
                |--------------------------------------------------------------------------
                */

                $orderedQty =
                    (float) (
                        $detail
                            ->purchaseOrderDetail
                            ?->qty
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Goods Receipt Received Quantity
                |--------------------------------------------------------------------------
                */

                $receivedQty =
                    (float) (
                        $detail
                            ->goodsReceiptDetail
                            ?->received_qty
                        ?? $detail->received_qty
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Previously Returned Quantity
                |--------------------------------------------------------------------------
                |
                | Exclude current Purchase Return.
                |
                */

                $alreadyReturnedQty =
                    PurchaseReturnDetail::query()
                        ->where(
                            'goods_receipt_detail_id',
                            $detail->goods_receipt_detail_id
                        )
                        ->where(
                            'purchase_return_header_id',
                            '!=',
                            $purchaseReturn->id
                        )
                        ->sum(
                            'returned_qty'
                        );


                /*
                |--------------------------------------------------------------------------
                | Returnable Quantity
                |--------------------------------------------------------------------------
                */

                $returnableQty =
                    max(
                        $receivedQty
                        -
                        (float) $alreadyReturnedQty,
                        0
                    );


                /*
                |--------------------------------------------------------------------------
                | Append Presentation Fields
                |--------------------------------------------------------------------------
                */

                $detail->ordered_qty =
                    $orderedQty;

                $detail->received_qty =
                    $receivedQty;

                $detail->already_returned_qty =
                    (float) $alreadyReturnedQty;

                $detail->returnable_qty =
                    $returnableQty;

                $detail->returned_qty =
                    (float) (
                        $detail->returned_qty
                        ?? 0
                    );

                $detail->unit_cost =
                    (float) (
                        $detail->unit_cost
                        ?? 0
                    );

                $detail->total_cost =
                    (float) (
                        $detail->total_cost
                        ?? 0
                    );


                return $detail;

            }
        );


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'data' =>
            $purchaseReturn,

    ]);

}


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdatePurchaseReturnRequest $request,
        PurchaseReturnHeader $purchaseReturn
    ) {

        abort_if(
            ! in_array(
                $purchaseReturn->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected purchase return can be updated.'
        );


        $data =
            $request->validated();


        $this
            ->purchaseReturnService
            ->updatePurchaseReturn(
                $purchaseReturn,
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase return updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        PurchaseReturnHeader $purchaseReturn
    ) {

        $this
            ->purchaseReturnService
            ->submitPurchaseReturn(
                $purchaseReturn
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase return submitted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        PurchaseReturnHeader $purchaseReturn
    ) {

        $this
            ->purchaseReturnService
            ->approvePurchaseReturn(
                $purchaseReturn
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase return approved successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        RejectPurchaseReturnRequest $request,
        PurchaseReturnHeader $purchaseReturn
    ) {

        $this
            ->purchaseReturnService
            ->rejectPurchaseReturn(
                $purchaseReturn,
                $request
                    ->validated()['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase return rejected successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        PurchaseReturnHeader $purchaseReturn
    ) {

        $this
            ->purchaseReturnService
            ->postPurchaseReturn(
                $purchaseReturn
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase return posted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        PurchaseReturnHeader $purchaseReturn
    ) {

        $validated =
            $request->validate([

                'reason' => [

                    'required',

                    'string',

                    'max:1000',

                ],

            ]);


        $this
            ->purchaseReturnService
            ->cancelPurchaseReturn(
                $purchaseReturn,
                $validated['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase return cancelled successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Bulk Delete
    |--------------------------------------------------------------------------
    */

    public function bulkDelete(
        Request $request
    ) {

        $validated =
            $request->validate([

                'ids' => [

                    'required',

                    'array',

                    'min:1',

                ],

                'ids.*' => [

                    'required',

                    'integer',

                    'exists:purchase_return_headers,id',

                ],

            ]);


        $this
            ->purchaseReturnService
            ->deletePurchaseReturns(
                $validated['ids']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase returns deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        PurchaseReturnHeader $purchaseReturn
    ) {

        $this
            ->purchaseReturnService
            ->deletePurchaseReturns([
                $purchaseReturn->id,
            ]);


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase return deleted successfully.'
            );
    }
}