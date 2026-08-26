<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;

use App\Http\Requests\Purchasing\GoodsReceive\StoreGoodsReceiptRequest;
use App\Http\Requests\Purchasing\GoodsReceive\UpdateGoodsReceiptRequest;
use App\Http\Requests\Purchasing\GoodsReceive\RejectGoodsReceiptRequest;

use App\Models\Purchasing\GoodsReceiptHeader;
use App\Models\Purchasing\PurchaseOrderHeader;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Unit;

use App\Models\Product\ProductVariant;

use App\Services\Purchasing\GoodsReceiptService;
use App\Services\Core\CodeGeneratorService;

use Illuminate\Http\Request;
use Inertia\Inertia;

class GoodsReceiptController extends Controller
{
    public function __construct(
        protected GoodsReceiptService $goodsReceiptService,
        protected CodeGeneratorService $codeGeneratorService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

public function index(Request $request)
{
    $query =
        GoodsReceiptHeader::query()
            ->with([
                'purchaseOrder',
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
                                    'grn_number',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'supplier_do_number',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhereHas(
                                    'purchaseOrder',
                                    function ($purchaseOrder) use ($search) {

                                        $purchaseOrder
                                            ->where(
                                                'number',
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
            | Receipt Date Filter
            |--------------------------------------------------------------------------
            */

            ->when(
                $request->filled('date_from'),
                function ($query) use ($request) {

                    $query->whereDate(
                        'receipt_date',
                        '>=',
                        $request->date_from
                    );

                }
            )

            ->when(
                $request->filled('date_to'),
                function ($query) use ($request) {

                    $query->whereDate(
                        'receipt_date',
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

    $goodsReceipts =
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

    $goodsReceipts
        ->getCollection()
        ->transform(
            function ($goodsReceipt) {

                $goodsReceipt->total_items =
                    $goodsReceipt
                        ->details
                        ->count();

                $goodsReceipt->total_received =
                    $goodsReceipt
                        ->details
                        ->sum(
                            fn ($detail) =>
                                (float)
                                $detail->received_qty
                        );

                $goodsReceipt->total_rejected =
                    $goodsReceipt
                        ->details
                        ->sum(
                            fn ($detail) =>
                                (float)
                                $detail->rejected_qty
                        );

                return $goodsReceipt;

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
        'Purchasing/GoodsReceipts/Index',

        [

            'title' =>
                'Goods Receipt',

            'goodsReceipts' =>
                $goodsReceipts,

            'statistics' =>
                $statistics,

            'previewNumber' =>
                $this
                    ->codeGeneratorService
                    ->preview(
                        'purchase_receive'
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
        | Purchase Orders
        |--------------------------------------------------------------------------
        */

        'purchaseOrders' =>
            PurchaseOrderHeader::query()
                ->whereIn(
                    'status',
                    [
                        'Confirmed',
                        'Partially Received',
                    ]
                )
                ->whereHas(
                    'details',
                    function ($query) {

                        $query->where(
                            'remaining_qty',
                            '>',
                            0
                        );

                    }
                )
                ->with([
                    'supplier',
                    'branch',
                    'warehouse',
                    'details' => function ($query) {

                        $query
                            ->where(
                                'remaining_qty',
                                '>',
                                0
                            )
                            ->with([
                                'productVariant.product',
                                'unit',
                            ]);

                    },
                ])
                ->orderByDesc(
                    'order_date'
                )
                ->orderByDesc(
                    'id'
                )
                ->get()
                ->map(
                    function ($purchaseOrder) {

                        return [

                            'id' =>
                                $purchaseOrder->id,

                            'number' =>
                                $purchaseOrder->number,

                            'label' =>
                                $purchaseOrder->number,

                            'order_date' =>
                                $purchaseOrder->order_date,

                            'required_date' =>
                                $purchaseOrder->required_date,

                            'company_id' =>
                                $purchaseOrder->company_id,

                            'branch_id' =>
                                $purchaseOrder->branch_id,

                            'warehouse_id' =>
                                $purchaseOrder->warehouse_id,

                            'supplier_id' =>
                                $purchaseOrder->supplier_id,

                            'supplier' => [

                                'id' =>
                                    $purchaseOrder
                                        ->supplier
                                        ?->id,

                                'code' =>
                                    $purchaseOrder
                                        ->supplier
                                        ?->supplier_code,

                                'name' =>
                                    $purchaseOrder
                                        ->supplier
                                        ?->name,

                            ],

                            'branch' => [

                                'id' =>
                                    $purchaseOrder
                                        ->branch
                                        ?->id,

                                'name' =>
                                    $purchaseOrder
                                        ->branch
                                        ?->name,

                            ],

                            'warehouse' => [

                                'id' =>
                                    $purchaseOrder
                                        ->warehouse
                                        ?->id,

                                'name' =>
                                    $purchaseOrder
                                        ->warehouse
                                        ?->name,

                            ],

                            'details' =>
                                $purchaseOrder
                                    ->details
                                    ->map(
                                        function ($detail) {

                                            return [

                                                'id' =>
                                                    $detail->id,

                                                'product_variant_id' =>
                                                    $detail
                                                        ->product_variant_id,

                                                'unit_id' =>
                                                    $detail
                                                        ->unit_id,

                                                'qty' =>
                                                    $detail->qty,

                                                'received_qty' =>
                                                    $detail
                                                        ->received_qty,

                                                'remaining_qty' =>
                                                    $detail
                                                        ->remaining_qty,

                                                'unit_price' =>
                                                    $detail
                                                        ->unit_price,

                                                'description' =>
                                                    $detail
                                                        ->description,

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


                            ->values(),
                           'inventory_movements' =>
    $goodsReceipt
        ->inventoryMovements
        ->map(
            function ($movement) {

                return [

                    'id' =>
                        $movement->id,

                    'product_variant_id' =>
                        $movement->product_variant_id,

                    'unit_id' =>
                        $movement->unit_id,

                    'reference_number' =>
                        $movement->reference_number,

                    'qty_in' =>
                        (float)
                        $movement->qty_in,

                    'qty_out' =>
                        (float)
                        $movement->qty_out,

                    'balance_qty' =>
                        (float)
                        $movement->balance_qty,

                    'unit_cost' =>
                        (float)
                        $movement->unit_cost,

                    'total_cost' =>
                        (float)
                        $movement->total_cost,

                    'transaction_date' =>
                        $movement
                            ->transaction_date
                            ?->format('Y-m-d'),

                    'description' =>
                        $movement->description,

                ];

            }
        )
        ->values(),
                        ];

                    }
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
        'Purchasing/GoodsReceipts/Create',

        [

            'title' =>
                'Create Goods Receipt',

            'previewNumber' =>
                $this
                    ->codeGeneratorService
                    ->preview(
                        'purchase_receive'
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
/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

public function edit(
    GoodsReceiptHeader $goodsReceipt
) {

    abort_if(
        $goodsReceipt->status !== 'Draft',
        422,
        'Only Draft goods receipt can be edited.'
    );


    $goodsReceipt->load([

        'purchaseOrder',

        'supplier',

        'warehouse',

        'details.productVariant.product',

        'details.unit',

    ]);


    return Inertia::render(
        'Purchasing/GoodsReceipts/Edit',

        [

            'title' =>
                'Edit Goods Receipt',

            'goodsReceipt' =>
                $goodsReceipt,

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
    StoreGoodsReceiptRequest $request
) {

    $data =
        $request->validated();

    $this
        ->goodsReceiptService
        ->createGoodsReceipt(
            $data
        );

    return redirect()
        ->back()
        ->with(
            'success',
            'Goods receipt created successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

public function show(
    GoodsReceiptHeader $goodsReceipt
) {

    $goodsReceipt->load([

        'purchaseOrder',

        'supplier',

        'warehouse',

        'creator',

        'updater',

        'poster',

        'canceller',

        'details.productVariant.product',

        'details.unit',

        'details.purchaseOrderDetail',

        'activities.performer',

        'inventoryMovements',

        'inventoryMovements.productVariant.product',

        'inventoryMovements.unit',

    ]);


    return Inertia::render(
        'Purchasing/GoodsReceipts/Show',

        [

            'goodsReceipt' =>
                $goodsReceipt,

        ]
    );
}
/*
|--------------------------------------------------------------------------
| Show Data
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Show Data
|--------------------------------------------------------------------------
*/

public function showData(
    GoodsReceiptHeader $goodsReceipt
) {

    $goodsReceipt->load([

        'purchaseOrder',

        'supplier',

        'warehouse',

        'creator',

        'updater',

        'poster',

        'canceller',

        'details.productVariant.product',

        'details.unit',

        'details.purchaseOrderDetail',

        'activities.performer',

        'inventoryMovements',
        'inventoryMovements.productVariant.product',

        'inventoryMovements.unit',

    ]);


    return response()->json([

        'data' =>
            $goodsReceipt,

    ]);

}
/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/
public function update(
    UpdateGoodsReceiptRequest $request,
    GoodsReceiptHeader $goodsReceipt
) {

    abort_if(
        ! in_array(
            $goodsReceipt->status,
            [
                'Draft',
                'Rejected',
            ],
            true
        ),
        422,
        'Only Draft or Rejected goods receipt can be updated.'
    );


    $data =
        $request->validated();


    $this
        ->goodsReceiptService
        ->updateGoodsReceipt(
            $goodsReceipt,
            $data
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Goods receipt updated successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

public function submit(
    GoodsReceiptHeader $goodsReceipt
) {

    $this
        ->goodsReceiptService
        ->submitGoodsReceipt(
            $goodsReceipt
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Goods receipt submitted successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

public function approve(
    GoodsReceiptHeader $goodsReceipt
) {

    $this
        ->goodsReceiptService
        ->approveGoodsReceipt(
            $goodsReceipt
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Goods receipt approved successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

public function reject(
    RejectGoodsReceiptRequest $request,
    GoodsReceiptHeader $goodsReceipt
) {

    $this
        ->goodsReceiptService
        ->rejectGoodsReceipt(
            $goodsReceipt,
            $request
                ->validated()['reason']
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Goods receipt rejected successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Post
|--------------------------------------------------------------------------
*/

public function post(
    GoodsReceiptHeader $goodsReceipt
) {

    $this
        ->goodsReceiptService
        ->postGoodsReceipt(
            $goodsReceipt
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Goods receipt posted successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

public function cancel(
    Request $request,
    GoodsReceiptHeader $goodsReceipt
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
        ->goodsReceiptService
        ->cancelGoodsReceipt(
            $goodsReceipt,
            $validated['reason']
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Goods receipt cancelled successfully.'
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
                'exists:goods_receipts_headers,id',
            ],

        ]);


    $this
        ->goodsReceiptService
        ->deleteGoodsReceipts(
            $validated['ids']
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Goods receipts deleted successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

public function destroy(
    GoodsReceiptHeader $goodsReceipt
) {

    $this
        ->goodsReceiptService
        ->deleteGoodsReceipts([
            $goodsReceipt->id,
        ]);


    return redirect()
        ->back()
        ->with(
            'success',
            'Goods receipt deleted successfully.'
        );
}
}