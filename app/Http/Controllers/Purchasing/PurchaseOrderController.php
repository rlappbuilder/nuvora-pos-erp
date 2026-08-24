<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;

use App\Http\Requests\Purchasing\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\Purchasing\PurchaseOrder\UpdatePurchaseOrderRequest;
use App\Http\Requests\Purchasing\PurchaseOrder\RejectPurchaseOrderRequest;
use App\Models\Purchasing\PurchaseRequestHeader;
use App\Models\Purchasing\PurchaseOrderHeader;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Unit;
use App\Models\MasterData\Supplier;

use App\Models\Product\ProductVariant;

use App\Services\Purchasing\PurchaseOrderService;
use App\Services\Core\CodeGeneratorService;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected PurchaseOrderService $purchaseOrderService,
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
            PurchaseOrderHeader::query()
                ->with([
                    'supplier',
                    'branch',
                    'warehouse',
                    'details.productVariant.product',
                    'details.unit',
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
                                        'number',
                                        'like',
                                        "%{$search}%"
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
                                        'branch',
                                        function ($branch) use ($search) {

                                            $branch->where(
                                                'name',
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
                | Branch Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('branch_id'),
                    function ($query) use ($request) {

                        $query->where(
                            'branch_id',
                            $request->branch_id
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


        /*/*
        |--------------------------------------------------------------------------
        | Order Date Filter
        |--------------------------------------------------------------------------
        */

        ->when(
            $request->filled('date_from'),
            function ($query) use ($request) {

                $query->whereDate(
                    'order_date',
                    '>=',
                    $request->date_from
                );

            }
        )

        ->when(
            $request->filled('date_to'),
            function ($query) use ($request) {

                $query->whereDate(
                    'order_date',
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

        $purchaseOrders =
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

        $purchaseOrders
            ->getCollection()
            ->transform(
                function ($purchaseOrder) {

                    $purchaseOrder->total_items =
                        $purchaseOrder
                            ->details
                            ->count();

                    return $purchaseOrder;

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

            'sent' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Sent'
                    )
                    ->count(),

            'confirmed' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Confirmed'
                    )
                    ->count(),

            'partially_received' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Partially Received'
                    )
                    ->count(),

            'fully_received' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Fully Received'
                    )
                    ->count(),

            'cancelled' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Cancelled'
                    )
                    ->count(),

            'closed' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Closed'
                    )
                    ->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Purchasing/PurchaseOrders/Index',

            array_merge(

                [

                    'title' =>
                        'Purchase Order',

                    'purchaseOrders' =>
                        $purchaseOrders,

                    'statistics' =>
                        $statistics,

                    'previewNumber' =>
                        $this
                            ->codeGeneratorService
                            ->preview(
                                'purchase_order'
                            ),

                    'filters' =>
                        $request->only([
                            'search',
                            'branch_id',
                            'warehouse_id',
                            'supplier_id',
                            'status',
                            'per_page',
                            'date_from',
                            'date_to',
                        ]),

                ],

                $this->formData()

            )
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
            | Branches
            |--------------------------------------------------------------------------
            */

            'branches' =>
                Branch::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'company_id',
                        'name',
                    ])
                    ->map(
                        fn ($branch) => [

                            'id' =>
                                $branch->id,

                            'company_id' =>
                                $branch->company_id,

                            'label' =>
                                $branch->name,

                        ]
                    )
                    ->values(),


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
                    | Purchase Requests
                    |--------------------------------------------------------------------------
                    */
                    'purchaseRequests' =>
                        PurchaseRequestHeader::query()
                            ->where(
                                'status',
                                'Approved'
                            )
                            ->whereDoesntHave(
                                'purchaseOrders'
                            )
                            ->with([
                                'details.productVariant.product',
                                'details.unit',
                            ])
                            ->orderByDesc(
                                'request_date'
                            )
                            ->orderByDesc(
                                'id'
                            )
                            ->get()
                            ->map(
                                fn ($purchaseRequest) => [

                                    'id' =>
                                        $purchaseRequest->id,

                                    'number' =>
                                        $purchaseRequest->number,

                                    'label' =>
                                        $purchaseRequest->number,

                                    'request_date' =>
                                        $purchaseRequest->request_date,

                                    'required_date' =>
                                        $purchaseRequest->required_date,

                                    'branch_id' =>
                                        $purchaseRequest->branch_id,

                                    'warehouse_id' =>
                                        $purchaseRequest->warehouse_id,

                                    'details' =>
                                        $purchaseRequest
                                            ->details
                                            ->map(
                                                fn ($detail) => [

                                                    'id' =>
                                                        $detail->id,

                                                    'product_variant_id' =>
                                                        $detail->product_variant_id,

                                                    'unit_id' =>
                                                        $detail->unit_id,

                                                    'qty' =>
                                                        $detail->qty,

                                                    'description' =>
                                                        $detail->description,

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

                                                ]
                                            )
                                            ->values(),

                                ]
                            )
                            ->values(),
               /*
                |--------------------------------------------------------------------------
                | Product Variant
                |--------------------------------------------------------------------------
                */

            'variants' =>
                ProductVariant::query()
                    ->active()
                    ->whereHas(
                        'units',
                        function ($query) {

                            $query->active();

                        }
                    )
                    ->with([

                        'product',

                        'units' => function ($query) {

                            $query
                                ->active()
                                ->with('unit')
                                ->orderBy('sort_order');

                        },

                    ])
                    ->orderBy('sku')
                    ->get([
                        'id',
                        'product_id',
                        'sku',
                        'name',
                    ])
                    ->map(
                        fn ($variant) => [

                            'id' =>
                                $variant->id,

                            'label' =>
                                implode(
                                    ' - ',
                                    array_filter([
                                        $variant->sku,
                                        $variant->product?->name,
                                        $variant->name,
                                    ])
                                ),

                            'units' =>
                                $variant
                                    ->units
                                    ->map(
                                        fn ($variantUnit) => [

                                            'id' =>
                                                $variantUnit->unit_id,

                                            'label' =>
                                                $variantUnit
                                                    ->unit
                                                    ?->name,

                                            'conversion_factor' =>
                                                $variantUnit
                                                    ->conversion_factor,

                                            'is_base' =>
                                                $variantUnit
                                                    ->is_base,

                                            'is_default' =>
                                                $variantUnit
                                                    ->is_default,

                                        ]
                                    )
                                    ->values(),

                        ]
                    )
                    ->values(),


            /*
            |--------------------------------------------------------------------------
            | Units
            |--------------------------------------------------------------------------
            */

            'units' =>
                Unit::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ])
                    ->map(
                        fn ($unit) => [

                            'id' =>
                                $unit->id,

                            'label' =>
                                $unit->name,

                        ]
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
                'Purchasing/PurchaseOrders/Create',

                array_merge(

                    [

                        'title' =>
                            'Create Purchase Order',

                        'previewNumber' =>
                            $this
                                ->codeGeneratorService
                                ->preview(
                                    'purchase_order'
                                ),

                    ],

                    $this->formData()

                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Edit
        |--------------------------------------------------------------------------
        */

        public function edit(
            PurchaseOrderHeader $purchaseOrder
        ) {

            abort_if(
                ! in_array(
                    $purchaseOrder->status,
                    [
                        'Draft',
                        'Rejected',
                    ],
                    true
                ),
                422,
                'Only Draft or Rejected purchase order can be edited.'
            );


            $purchaseOrder->load([

                'supplier',

                'branch',

                'warehouse',

                'purchaseRequest',

                'details.productVariant.product',

                'details.unit',

            ]);


            return Inertia::render(
                'Purchasing/PurchaseOrders/Edit',

                array_merge(

                    [

                        'title' =>
                            'Edit Purchase Order',

                        'purchaseOrder' =>
                            $purchaseOrder,

                    ],

                    $this->formData()

                )
            );
        }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */
    public function store(
        StorePurchaseOrderRequest $request
    ) {

        $data =
            $request->validated();


        $branch =
            Branch::findOrFail(
                $data['branch_id']
            );


        $data['company_id'] =
            $branch->company_id;


        $this
            ->purchaseOrderService
            ->createPurchaseOrder(
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase order created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        PurchaseOrderHeader $purchaseOrder
    ) {

        $purchaseOrder->load([

            'company',

            'branch',

            'warehouse',

            'supplier',

            'purchaseRequest',

            'creator',

            'updater',

            'approver',

            'rejector',

            'sender',

            'confirmer',

            'canceller',

            'deleter',

            'details.productVariant.product',

            'details.unit',

            'activities.performer',

        ]);


        return Inertia::render(
            'Purchasing/PurchaseOrders/Show',

            [

                'purchaseOrder' =>
                    $purchaseOrder,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdatePurchaseOrderRequest $request,
        PurchaseOrderHeader $purchaseOrder
    ) {

        abort_if(
            ! in_array(
                $purchaseOrder->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected purchase order can be updated.'
        );


        $data =
            $request->validated();


        $branch =
            Branch::findOrFail(
                $data['branch_id']
            );


        $data['company_id'] =
            $branch->company_id;


        $this
            ->purchaseOrderService
            ->updatePurchaseOrder(
                $purchaseOrder,
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase order updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        PurchaseOrderHeader $purchaseOrder
    ) {

        $this
            ->purchaseOrderService
            ->submitPurchaseOrder(
                $purchaseOrder
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase order submitted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        PurchaseOrderHeader $purchaseOrder
    ) {

        $this
            ->purchaseOrderService
            ->approvePurchaseOrder(
                $purchaseOrder
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase order approved successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        RejectPurchaseOrderRequest $request,
        PurchaseOrderHeader $purchaseOrder
    ) {

        $this
            ->purchaseOrderService
            ->rejectPurchaseOrder(
                $purchaseOrder,
                $request
                    ->validated()['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase order rejected successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Send
    |--------------------------------------------------------------------------
    */

    public function send(
        PurchaseOrderHeader $purchaseOrder
    ) {

        $this
            ->purchaseOrderService
            ->sendPurchaseOrder(
                $purchaseOrder
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase order sent to supplier successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Confirm
    |--------------------------------------------------------------------------
    */

    public function confirm(
        PurchaseOrderHeader $purchaseOrder
    ) {

        $this
            ->purchaseOrderService
            ->confirmPurchaseOrder(
                $purchaseOrder
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase order confirmed successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        PurchaseOrderHeader $purchaseOrder
    ) {

        $this
            ->purchaseOrderService
            ->deletePurchaseOrders([
                $purchaseOrder->id,
            ]);


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase order deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate
    |--------------------------------------------------------------------------
    */

    public function duplicate(
        PurchaseOrderHeader $purchaseOrder
    ) {

        $this
            ->purchaseOrderService
            ->duplicatePurchaseOrder(
                $purchaseOrder
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase order duplicated successfully.'
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
                    'exists:purchase_order_headers,id',
                ],

            ]);


        $this
            ->purchaseOrderService
            ->deletePurchaseOrders(
                $validated['ids']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase orders deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

    public function showData(
        PurchaseOrderHeader $purchaseOrder
    ) {

        $purchaseOrder->load([

            'company',

            'branch',

            'warehouse',

            'supplier',

            'purchaseRequest',

            'creator',

            'updater',

            'approver',

            'rejector',

            'sender',

            'confirmer',

            'canceller',

            'deleter',

            'details.productVariant.product',

            'details.unit',

            'activities.performer',

        ]);


        return response()->json([

            'data' =>
                $purchaseOrder,

        ]);

    }
    /*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

public function cancel(
    Request $request,
    PurchaseOrderHeader $purchaseOrder
) {

    $validated = $request->validate([

        'reason' => [
            'required',
            'string',
            'max:1000',
        ],

    ]);


    /*
    |--------------------------------------------------------------------------
    | Validate Status
    |--------------------------------------------------------------------------
    */

    if (! in_array(
        $purchaseOrder->status,
        [
            'Approved',
            'Sent',
            'Confirmed',
        ],
        true
    )) {

        return back()->withErrors([

            'status' =>
                'Purchase Order cannot be cancelled in its current status.',

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    $purchaseOrder->update([

        'status' =>
            'Cancelled',

        'cancelled_at' =>
            now(),

        'cancelled_by' =>
            auth()->id(),

        'cancelled_reason' =>
            $validated['reason'],

        'updated_by' =>
            auth()->id(),

    ]);


    return back()->with(
        'success',
        'Purchase Order cancelled successfully.'
    );

}
}