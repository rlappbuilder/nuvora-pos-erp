<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;

use App\Http\Requests\Purchasing\PurchaseInvoice\StorePurchaseInvoiceRequest;
use App\Http\Requests\Purchasing\PurchaseInvoice\UpdatePurchaseInvoiceRequest;
use App\Http\Requests\Purchasing\PurchaseInvoice\RejectPurchaseInvoiceRequest;

use App\Models\Purchasing\PurchaseInvoiceHeader;
use App\Models\Purchasing\PurchaseInvoiceDetail;
use App\Models\Purchasing\PurchaseOrderHeader;
use App\Models\Purchasing\GoodsReceiptHeader;
use App\Http\Requests\Accounting\UpdateFiscalYearRequest;
use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\PaymentTerm;
use App\Models\MasterData\Currency;
use App\Models\MasterData\Tax;
use App\Models\MasterData\Unit;

use App\Models\Product\ProductVariant;

use App\Services\Purchasing\PurchaseInvoiceService;
use App\Services\Core\CodeGeneratorService;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchaseInvoiceController extends Controller
{
    public function __construct(
        protected PurchaseInvoiceService $purchaseInvoiceService,
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
        PurchaseInvoiceHeader::query()
            ->with([
                'company',
                'branch',
                'purchaseOrder',
                'goodsReceipt',
                'supplier',
                'warehouse',
                'paymentTerm',
                'currency',
                'tax',
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

                                ->orWhere(
                                    'invoice_number',
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
            | Invoice Date Filter
            |--------------------------------------------------------------------------
            */

            ->when(
                $request->filled('date_from'),
                function ($query) use ($request) {

                    $query->whereDate(
                        'invoice_date',
                        '>=',
                        $request->date_from
                    );

                }
            )

            ->when(
                $request->filled('date_to'),
                function ($query) use ($request) {

                    $query->whereDate(
                        'invoice_date',
                        '<=',
                        $request->date_to
                    );

                }
            )

            /*
            |--------------------------------------------------------------------------
            | Due Date Filter
            |--------------------------------------------------------------------------
            */

            ->when(
                $request->filled('due_date_from'),
                function ($query) use ($request) {

                    $query->whereDate(
                        'due_date',
                        '>=',
                        $request->due_date_from
                    );

                }
            )

            ->when(
                $request->filled('due_date_to'),
                function ($query) use ($request) {

                    $query->whereDate(
                        'due_date',
                        '<=',
                        $request->due_date_to
                    );

                }
            );


    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $purchaseInvoices =
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

    $purchaseInvoices
        ->getCollection()
        ->transform(
            function ($purchaseInvoice) {

                $purchaseInvoice->total_items =
                    $purchaseInvoice
                        ->details
                        ->count();

                return $purchaseInvoice;

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

        'partially_paid' =>
            (clone $statisticsQuery)
                ->where(
                    'status',
                    'Partially Paid'
                )
                ->count(),

        'paid' =>
            (clone $statisticsQuery)
                ->where(
                    'status',
                    'Paid'
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
        'Purchasing/PurchaseInvoice/Index',

        [

            'title' =>
                'Purchase Invoice',

            'purchaseInvoices' =>
                $purchaseInvoices,

            'statistics' =>
                $statistics,

            'previewNumber' =>
                $this
                    ->codeGeneratorService
                    ->preview(
                        'purchase_invoice'
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

                    'due_date_from',

                    'due_date_to',

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
        | Companies
        |--------------------------------------------------------------------------
        */

        'companies' =>
            Company::query()
                ->where(
                    'status',
                    true
                )
                ->orderBy('company_name')
                ->get([
                    'id',
                    'company_code',
                    'company_name',
                ])
                ->map(
                    fn ($company) => [

                        'id' =>
                            $company->id,

                        'code' =>
                            $company->company_code,

                        'label' =>
                            implode(
                                ' - ',
                                array_filter([
                                    $company->company_code,
                                    $company->company_name,
                                ])
                            ),

                    ]
                )
                ->values(),


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
        | Purchase Orders
        |--------------------------------------------------------------------------
        |
        | Hanya Purchase Order yang sudah Confirmed
        | dan masih memiliki receiving/invoice yang dapat
        | menjadi sumber Purchase Invoice.
        |
        */

        'purchaseOrders' =>
            PurchaseOrderHeader::query()
                ->where(
                    'status',
                    'Confirmed'
                )
                ->with([
                    'supplier',
                    'warehouse',
                    'branch',
                    'details.productVariant.product',
                    'details.unit',
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

                            'details' =>
                                $purchaseOrder
                                    ->details
                                    ->map(
                                        fn ($detail) => [

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
                                                $detail->unit_price,

                                            'discount_rate' =>
                                                $detail
                                                    ->discount_rate,

                                            'tax_rate' =>
                                                $detail->tax_rate,

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

                                        ]
                                    )
                                    ->values(),

                        ];

                    }
                )
                ->values(),


        /*
        |--------------------------------------------------------------------------
        | Goods Receipts
        |--------------------------------------------------------------------------
        |
        | Hanya GRN Posted yang dapat menjadi
        | sumber Purchase Invoice.
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
                    'branch',
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
                                $goodsReceipt
                                    ->purchase_order_id,

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
                                        fn ($detail) => [

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
                                                $detail
                                                    ->received_qty,

                                            'rejected_qty' =>
                                                $detail
                                                    ->rejected_qty,

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

                                        ]
                                    )
                                    ->values(),

                        ];

                    }
                )
                ->values(),


        /*
        |--------------------------------------------------------------------------
        | Payment Terms
        |--------------------------------------------------------------------------
        */

        'paymentTerms' =>
            PaymentTerm::query()
                ->where(
                    'status',
                    true
                )
                ->orderBy('days')
                ->orderBy('name')
                ->get([
                    'id',
                    'code',
                    'name',
                    'days',
                    'description',
                ])
                ->map(
                    fn ($paymentTerm) => [

                        'id' =>
                            $paymentTerm->id,

                        'code' =>
                            $paymentTerm->code,

                        'label' =>
                            $paymentTerm->name,

                        'days' =>
                            $paymentTerm->days,

                        'description' =>
                            $paymentTerm->description,

                    ]
                )
                ->values(),


        /*
        |--------------------------------------------------------------------------
        | Currencies
        |--------------------------------------------------------------------------
        */

        'currencies' =>
            Currency::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('code')
                ->get([
                    'id',
                    'code',
                    'name',
                    'symbol',
                    'decimal_places',
                    'exchange_rate',
                ])
                ->map(
                    fn ($currency) => [

                        'id' =>
                            $currency->id,

                        'code' =>
                            $currency->code,

                        'label' =>
                            implode(
                                ' - ',
                                array_filter([
                                    $currency->code,
                                    $currency->name,
                                ])
                            ),

                        'symbol' =>
                            $currency->symbol,

                        'decimal_places' =>
                            $currency->decimal_places,

                        'exchange_rate' =>
                            $currency->exchange_rate,

                    ]
                )
                ->values(),


        /*
        |--------------------------------------------------------------------------
        | Taxes
        |--------------------------------------------------------------------------
        */

        'taxes' =>
            Tax::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('code')
                ->get([
                    'id',
                    'code',
                    'name',
                    'type',
                    'rate',
                    'is_default',
                ])
                ->map(
                    fn ($tax) => [

                        'id' =>
                            $tax->id,

                        'code' =>
                            $tax->code,

                        'label' =>
                            implode(
                                ' - ',
                                array_filter([
                                    $tax->code,
                                    $tax->name,
                                ])
                            ),

                        'type' =>
                            $tax->type,

                        'rate' =>
                            $tax->rate,

                        'is_default' =>
                            $tax->is_default,

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
        'Purchasing/PurchaseInvoice/Create',

        [

            'title' =>
                'Create Purchase Invoice',

            'previewNumber' =>
                $this
                    ->codeGeneratorService
                    ->preview(
                        'purchase_invoice'
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
    PurchaseInvoiceHeader $purchaseInvoice
) {

    abort_if(
        ! in_array(
            $purchaseInvoice->status,
            [
                'Draft',
                'Rejected',
            ],
            true
        ),
        422,
        'Only Draft or Rejected purchase invoice can be edited.'
    );


    $purchaseInvoice->load([

        'company',

        'branch',

        'purchaseOrder',

        'goodsReceipt',

        'supplier',

        'warehouse',

        'paymentTerm',

        'currency',

        'tax',

        'details.productVariant.product',

        'details.unit',

        'details.purchaseOrderDetail',

        'details.goodsReceiptDetail',

    ]);


    return Inertia::render(
        'Purchasing/PurchaseInvoice/Edit',

        [

            'title' =>
                'Edit Purchase Invoice',

            'purchaseInvoice' =>
                $purchaseInvoice,

            ...$this->formData(),

        ]
    );
}
/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

public function store(
    StorePurchaseInvoiceRequest $request
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
        ->purchaseInvoiceService
        ->createPurchaseInvoice(
            $data
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Purchase invoice created successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

public function show(
    PurchaseInvoiceHeader $purchaseInvoice
) {

    $purchaseInvoice->load([

        'company',

        'branch',

        'purchaseOrder',

        'goodsReceipt',

        'supplier',

        'warehouse',

        'paymentTerm',

        'currency',

        'tax',

        'creator',

        'updater',

        'submitter',

        'approver',

        'rejector',

        'poster',

        'canceller',

        'details.productVariant.product',

        'details.unit',

        'details.purchaseOrderDetail',

        'details.goodsReceiptDetail',

        'activities.performer',

    ]);


    return Inertia::render(
        'Purchasing/PurchaseInvoice/Show',

        [

            'purchaseInvoice' =>
                $purchaseInvoice,

        ]
    );
}


/*
|--------------------------------------------------------------------------
| Show Data
|--------------------------------------------------------------------------
*/

public function showData(
    PurchaseInvoiceHeader $purchaseInvoice
) {

    $purchaseInvoice->load([

        'company',

        'branch',

        'purchaseOrder',

        'goodsReceipt',

        'supplier',

        'warehouse',

        'paymentTerm',

        'currency',

        'tax',

        'creator',

        'updater',

        'submitter',

        'approver',

        'rejector',

        'poster',

        'canceller',

        'details.productVariant.product',

        'details.unit',

        'details.purchaseOrderDetail',

        'details.goodsReceiptDetail',

        'activities.performer',

    ]);


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return response()->json([

        'data' =>
            $purchaseInvoice,

    ]);

}
/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

public function update(
    UpdateFiscalYearRequest $request,
    FiscalYear $fiscalYear
): RedirectResponse {

    $data =
        $request->validated();


    $data['updated_by'] =
        $request->user()->id;


    $this
        ->fiscalYearService
        ->updateFiscalYear(
            $fiscalYear,
            $data
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Fiscal year updated successfully.'
        );

}
/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

public function submit(
    PurchaseInvoiceHeader $purchaseInvoice
) {

    $this
        ->purchaseInvoiceService
        ->submitPurchaseInvoice(
            $purchaseInvoice
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Purchase invoice submitted successfully.'
        );
}


/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

public function approve(
    PurchaseInvoiceHeader $purchaseInvoice
) {

    $this
        ->purchaseInvoiceService
        ->approvePurchaseInvoice(
            $purchaseInvoice
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Purchase invoice approved successfully.'
        );
}

    /*
|--------------------------------------------------------------------------
| Post
|--------------------------------------------------------------------------
*/

public function post(
    PurchaseInvoiceHeader $purchaseInvoice
) {

    $this
        ->purchaseInvoiceService
        ->postPurchaseInvoice(
            $purchaseInvoice
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Purchase invoice posted successfully.'
        );
}

/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

public function reject(
    RejectPurchaseInvoiceRequest $request,
    PurchaseInvoiceHeader $purchaseInvoice
) {

    $this
        ->purchaseInvoiceService
        ->rejectPurchaseInvoice(
            $purchaseInvoice,
            $request
                ->validated()['reason']
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Purchase invoice rejected successfully.'
        );
}
/*
|--------------------------------------------------------------------------
| Destroy
|--------------------------------------------------------------------------
*/

public function destroy(
    PurchaseInvoiceHeader $purchaseInvoice
) {

    $this
        ->purchaseInvoiceService
        ->deletePurchaseInvoices([
            $purchaseInvoice->id,
        ]);


    return redirect()
        ->back()
        ->with(
            'success',
            'Purchase invoice deleted successfully.'
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
                'exists:purchase_invoice_headers,id',
            ],

        ]);


    $this
        ->purchaseInvoiceService
        ->deletePurchaseInvoices(
            $validated['ids']
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Purchase invoices deleted successfully.'
        );
}

}