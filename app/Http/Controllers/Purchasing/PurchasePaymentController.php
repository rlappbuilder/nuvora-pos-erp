<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;

use App\Http\Requests\Purchasing\PurchasePayment\StorePurchasePaymentRequest;
use App\Http\Requests\Purchasing\PurchasePayment\UpdatePurchasePaymentRequest;
use App\Http\Requests\Purchasing\PurchasePayment\RejectPurchasePaymentRequest;

use App\Models\Purchasing\PurchasePaymentHeader;
use App\Models\Purchasing\PurchaseInvoiceHeader;

use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Supplier;

use App\Models\Accounting\ChartOfAccount;

use App\Services\Purchasing\PurchasePaymentService;
use App\Services\Core\CodeGeneratorService;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PurchasePaymentController extends Controller
{
    public function __construct(
        protected PurchasePaymentService $purchasePaymentService,
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
            PurchasePaymentHeader::query()
                ->with([
                    'company',
                    'branch',
                    'supplier',
                    'paymentAccount',
                    'details.purchaseInvoice',
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
                                        'payment_method',
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
                                        'details.purchaseInvoice',
                                        function ($invoice) use ($search) {

                                            $invoice
                                                ->where(
                                                    'number',
                                                    'like',
                                                    "%{$search}%"
                                                )

                                                ->orWhere(
                                                    'invoice_number',
                                                    'like',
                                                    "%{$search}%"
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

                /*
                |--------------------------------------------------------------------------
                | Payment Date Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('date_from'),
                    function ($query) use ($request) {

                        $query->whereDate(
                            'payment_date',
                            '>=',
                            $request->date_from
                        );

                    }
                )

                ->when(
                    $request->filled('date_to'),
                    function ($query) use ($request) {

                        $query->whereDate(
                            'payment_date',
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

        $purchasePayments =
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

        $purchasePayments
            ->getCollection()
            ->transform(
                function ($purchasePayment) {

                    $purchasePayment->total_items =
                        $purchasePayment
                            ->details
                            ->count();

                    return $purchasePayment;

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

            'rejected' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Rejected'
                    )
                    ->count(),

            'approved' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Approved'
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
            'Purchasing/Purchasepayment/Index',

            array_merge(

                [

                    'title' =>
                        'Purchase Payment',

                    'purchasePayments' =>
                        $purchasePayments,

                    'statistics' =>
                        $statistics,

                    'previewNumber' =>
                        $this
                            ->codeGeneratorService
                            ->preview(
                                'purchase_payment'
                            ),

                    'filters' =>
                        $request->only([
                            'search',
                            'branch_id',
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
            | Payment Accounts
            |--------------------------------------------------------------------------
            */

            'paymentAccounts' =>
                ChartOfAccount::query()
                    ->where(
                        'status',
                        true
                    )
                    ->where(
                        'is_posting',
                        true
                    )
                    ->whereIn(
                        'normal_balance',
                        [
                            'Debit',
                        ]
                    )
                    ->whereHas(
                        'accountCategory',
                        function ($query) {

                            $query->whereIn(
                                'name',
                                [
                                    'Cash & Bank',
                                ]
                            );

                        }
                    )
                    ->orderBy('code')
                    ->get([
                        'id',
                        'company_id',
                        'code',
                        'name',
                    ])
                    ->map(
                        fn ($account) => [

                            'id' =>
                                $account->id,

                            'company_id' =>
                                $account->company_id,

                            'code' =>
                                $account->code,

                            'label' =>
                                $account->code .
                                ' - ' .
                                $account->name,

                        ]
                    )
                    ->values(),

            /*
            |--------------------------------------------------------------------------
            | Purchase Invoices
            |--------------------------------------------------------------------------
            */

            'purchaseInvoices' =>
                PurchaseInvoiceHeader::query()
                    ->whereIn(
                        'status',
                        [
                            'Posted',
                            'Partially Paid',
                        ]
                    )
                    ->where(
                        'outstanding_amount',
                        '>',
                        0
                    )
                    ->with([
                        'supplier',
                        'branch',
                    ])
                    ->orderByDesc(
                        'invoice_date'
                    )
                    ->orderByDesc(
                        'id'
                    )
                    ->get()
                    ->map(
                        fn ($invoice) => [

                            'id' =>
                                $invoice->id,

                            'number' =>
                                $invoice->number,

                            'invoice_number' =>
                                $invoice->invoice_number,

                            'invoice_date' =>
                                $invoice->invoice_date,

                            'company_id' =>
                                $invoice->company_id,

                            'branch_id' =>
                                $invoice->branch_id,

                            'supplier_id' =>
                                $invoice->supplier_id,

                            'supplier' => [

                                'id' =>
                                    $invoice
                                        ->supplier
                                        ?->id,

                                'code' =>
                                    $invoice
                                        ->supplier
                                        ?->supplier_code,

                                'name' =>
                                    $invoice
                                        ->supplier
                                        ?->name,

                            ],

                            'grand_total' =>
                                $invoice->grand_total,

                            'paid_amount' =>
                                $invoice->paid_amount,

                            'outstanding_amount' =>
                                $invoice->outstanding_amount,

                            'status' =>
                                $invoice->status,

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
            'Purchasing/Purchasepayment/Create',

            array_merge(

                [

                    'title' =>
                        'Create Purchase Payment',

                    'previewNumber' =>
                        $this
                            ->codeGeneratorService
                            ->preview(
                                'purchase_payment'
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
        PurchasePaymentHeader $purchasePayment
    ) {

        abort_if(
            ! in_array(
                $purchasePayment->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected purchase payment can be edited.'
        );


        $purchasePayment->load([

            'company',
            'branch',
            'supplier',
            'paymentAccount',

            'details.purchaseInvoice',

        ]);


        return Inertia::render(
            'Purchasing/Purchasepayment/Edit',

            array_merge(

                [

                    'title' =>
                        'Edit Purchase Payment',

                    'purchasePayment' =>
                        $purchasePayment,

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
        StorePurchasePaymentRequest $request
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
            ->purchasePaymentService
            ->create(
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase payment created successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        PurchasePaymentHeader $purchasePayment
    ) {

        $purchasePayment->load([

            'company',
            'branch',
            'supplier',
            'paymentAccount',

            'creator',
            'updater',
            'submitter',
            'approver',
            'poster',
            'canceller',
            'rejector',
            'details.purchaseInvoice',

            'activities.performer',

        ]);


        return Inertia::render(
            'Purchasing/Purchasepayment/Show',

            [

                'purchasePayment' =>
                    $purchasePayment,

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdatePurchasePaymentRequest $request,
        PurchasePaymentHeader $purchasePayment
    ) {

        abort_if(
            ! in_array(
                $purchasePayment->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected purchase payment can be updated.'
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
            ->purchasePaymentService
            ->update(
                $purchasePayment,
                $data
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase payment updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        PurchasePaymentHeader $purchasePayment
    ) {

        $this
            ->purchasePaymentService
            ->submit(
                $purchasePayment
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase payment submitted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        PurchasePaymentHeader $purchasePayment
    ) {

        $this
            ->purchasePaymentService
            ->approve(
                $purchasePayment
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase payment approved successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        RejectPurchasePaymentRequest $request,
        PurchasePaymentHeader $purchasePayment
    ) {

        $this
            ->purchasePaymentService
            ->reject(
                $purchasePayment,
                $request
                    ->validated()['reason']
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase payment rejected successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        PurchasePaymentHeader $purchasePayment
    ) {

        $this
            ->purchasePaymentService
            ->post(
                $purchasePayment
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Purchase payment posted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        PurchasePaymentHeader $purchasePayment
    ) {

        $validated =
            $request->validate([

                'reason' => [
                    'required',
                    'string',
                    'max:1000',
                ],

            ]);


        if (! in_array(
            $purchasePayment->status,
            [
                'Approved',
            ],
            true
        )) {

            return back()->withErrors([

                'status' =>
                    'Purchase Payment cannot be cancelled in its current status.',

            ]);

        }


        $this
            ->purchasePaymentService
            ->cancel(
                $purchasePayment,
                $validated['reason']
            );


        return back()
            ->with(
                'success',
                'Purchase payment cancelled successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

    public function showData(
        PurchasePaymentHeader $purchasePayment
    ) {

        $purchasePayment->load([

            'company',
            'branch',
            'supplier',
            'paymentAccount',

            'creator',
            'updater',
            'submitter',
            'approver',
            'poster',
            'canceller',
            'rejector',
            'details.purchaseInvoice',

            'activities.performer',

        ]);


        return response()->json([

            'data' =>
                $purchasePayment,

        ]);
    }
}