<?php

namespace App\Http\Controllers\Consignment;

use App\Http\Controllers\Controller;

use App\Http\Requests\Reseller\ConsignmentReceivable\StoreConsignmentReceivableRequest;
use App\Http\Requests\Reseller\ConsignmentReceivable\UpdateConsignmentReceivableRequest;
use App\Http\Requests\Reseller\ConsignmentReceivable\RejectConsignmentReceivableRequest;

use App\Models\Reseller\ConsignmentReceivable\ConsignmentReceivableHeader;
use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;
use App\Models\Reseller\Reseller;

use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;

use App\Models\Accounting\ChartOfAccount;

use App\Services\Consignment\ConsignmentReceivableService;
use App\Services\Core\CodeGeneratorService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ConsignmentReceivableController extends Controller
{
    public function __construct(
        protected ConsignmentReceivableService $consignmentReceivableService,
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
            ConsignmentReceivableHeader::query()
                ->with([
                    'company',
                    'branch',
                    'reseller',
                    'paymentAccount',
                    'details.settlement',
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
                                        'reseller',
                                        function ($reseller) use ($search) {

                                            $reseller
                                                ->where(
                                                    'name',
                                                    'like',
                                                    "%{$search}%"
                                                )

                                                ->orWhere(
                                                    'reseller_code',
                                                    'like',
                                                    "%{$search}%"
                                                );
                                        }
                                    )

                                    ->orWhereHas(
                                        'details.settlement',
                                        function ($settlement) use ($search) {

                                            $settlement
                                                ->where(
                                                    'settlement_number',
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
                | Reseller Filter
                |--------------------------------------------------------------------------
                */

                ->when(
                    $request->filled('reseller_id'),
                    function ($query) use ($request) {

                        $query->where(
                            'reseller_id',
                            $request->reseller_id
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
                    });

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $consignmentReceivables =
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

        $consignmentReceivables
            ->getCollection()
            ->transform(
                function ($receivable) {

                    $receivable->total_items =
                        $receivable
                            ->details
                            ->count();

                    return $receivable;
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
            ->sum('total_amount'),

    'posted' =>
        (clone $statisticsQuery)
            ->where('status', 'Posted')
            ->sum('total_amount'),

    'draft' =>
        (clone $statisticsQuery)
            ->where(
                'status',
                'Draft'
            )
            ->sum('total_amount'),

    'submitted' =>
        (clone $statisticsQuery)
            ->where(
                'status',
                'Submitted'
            )
            ->sum('total_amount'),

    'rejected' =>
        (clone $statisticsQuery)
            ->where(
                'status',
                'Rejected'
            )
            ->sum('total_amount'),

    'approved' =>
        (clone $statisticsQuery)
            ->where(
                'status',
                'Approved'
            )
            ->sum('total_amount'),

   'total_transaction' =>
        (clone $statisticsQuery)
            ->count(),

    'cancelled' =>
        (clone $statisticsQuery)
            ->where(
                'status',
                'Cancelled'
            )
            ->sum('total_amount'),

];

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Resellers/ConsignmentReceivable/Index',

            array_merge(

                [

                    'title' =>
                        'Consignment Receivable',

                    'receivables' =>
                        $consignmentReceivables,

                    'statistics' =>
                        $statistics,

                    'previewNumber' =>
                        $this
                            ->codeGeneratorService
                            ->preview(
                                'consignment_receivable'
                            ),

                    'filters' =>
                        $request->only([
                            'search',
                            'branch_id',
                            'reseller_id',
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
private function formData(): array
{
    /*
    |--------------------------------------------------------------------------
    | User Company
    |--------------------------------------------------------------------------
    */

    $userCompanyId =
        auth()->user()->company_id;


    /*
    |--------------------------------------------------------------------------
    | Companies
    |--------------------------------------------------------------------------
    */

    $companies =
        Company::query()
            ->where(
                'status',
                true
            )
            ->when(
                $userCompanyId,
                function ($query) use ($userCompanyId) {

                    $query->where(
                        'id',
                        $userCompanyId
                    );

                }
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
            ->values();


    /*
    |--------------------------------------------------------------------------
    | Branches
    |--------------------------------------------------------------------------
    |
    | Only branches belonging to user's company.
    |--------------------------------------------------------------------------
    */

    $branches =
        Branch::query()
            ->when(
                $userCompanyId,
                function ($query) use ($userCompanyId) {

                    $query->where(
                        'company_id',
                        $userCompanyId
                    );

                }
            )
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
            ->values();


    /*
    |--------------------------------------------------------------------------
    | Resellers
    |--------------------------------------------------------------------------
    |
    | Reseller is company-level.
    |--------------------------------------------------------------------------
    */

    $resellers =
        Reseller::query()
            ->where(
                'status',
                true
            )
            ->when(
                $userCompanyId,
                fn ($query) =>
                    $query->where(
                        'company_id',
                        $userCompanyId
                    )
            )
            ->orderBy('name')
            ->get([
                'id',
                'company_id',
                'reseller_code',
                'name',
                'contact_person',
            ])
            ->map(
                fn ($reseller) => [

                    'id' =>
                        $reseller->id,

                    'company_id' =>
                        $reseller->company_id,

                    'code' =>
                        $reseller->reseller_code,

                    'label' =>
                        implode(
                            ' - ',
                            array_filter([
                                $reseller->reseller_code,
                                $reseller->name,
                            ])
                        ),

                    'contact_person' =>
                        $reseller->contact_person,

                ]
            )
            ->values();


    /*
    |--------------------------------------------------------------------------
    | Payment Accounts
    |--------------------------------------------------------------------------
    |
    | Only active posting Cash & Bank accounts belonging to
    | the logged-in user's company.
    |--------------------------------------------------------------------------
    */

    $paymentAccounts =
        ChartOfAccount::query()
            ->where(
                'company_id',
                $userCompanyId
            )
            ->where(
                'status',
                true
            )
            ->where(
                'is_posting',
                true
            )
            ->where(
                'normal_balance',
                'Debit'
            )
            ->whereHas(
                'accountCategory',
                function ($query) {

                    $query->where(
                        'name',
                        'Cash & Bank'
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
            ->values();


    /*
    |--------------------------------------------------------------------------
    | Posted Settlements
    |--------------------------------------------------------------------------
    |
    | Only Posted settlements belonging to user's company
    | and having an original receivable amount.
    |--------------------------------------------------------------------------
    */

    $settlements =
        ConsignmentSettlementHeader::query()
            ->where(
                'company_id',
                $userCompanyId
            )
            ->where(
                'status',
                'Posted'
            )
            ->where(
                'receivable_amount',
                '>',
                0
            )
            ->with([
                'reseller',
                'branch',
            ])
            ->orderByDesc(
                'settlement_date'
            )
            ->orderByDesc(
                'id'
            )
            ->get();


    /*
    |--------------------------------------------------------------------------
    | Settlement IDs
    |--------------------------------------------------------------------------
    */

    $settlementIds =
        $settlements
            ->pluck('id')
            ->values();


    /*
    |--------------------------------------------------------------------------
    | Previous Consignment Receivable Payments
    |--------------------------------------------------------------------------
    |
    | Only Posted Consignment Receivable transactions are counted.
    |
    | Payment made directly on Settlement is NOT included here.
    |--------------------------------------------------------------------------
    */

    $previousReceivablePayments =
        collect();

    if (
        $settlementIds->isNotEmpty()
    ) {

        $previousReceivablePayments =
            DB::table(
                'consignment_receivable_details'
            )
            ->join(
                'consignment_receivable_headers',
                'consignment_receivable_headers.id',
                '=',
                'consignment_receivable_details.consignment_receivable_header_id'
            )
            ->whereIn(
                'consignment_receivable_details.settlement_header_id',
                $settlementIds
            )
            ->where(
                'consignment_receivable_headers.status',
                'Posted'
            )
            ->select(
                'consignment_receivable_details.settlement_header_id',
                DB::raw(
                    'SUM(consignment_receivable_details.payment_amount) AS total_paid'
                )
            )
            ->groupBy(
                'consignment_receivable_details.settlement_header_id'
            )
            ->pluck(
                'total_paid',
                'settlement_header_id'
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Settlement Data
    |--------------------------------------------------------------------------
    */

    $settlementData =
        $settlements
            ->map(
                function ($settlement) use (
                    $previousReceivablePayments
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Payment made directly on Settlement
                    |--------------------------------------------------------------------------
                    |
                    | This payment has already reduced receivable_amount.
                    | It is NOT Previous Paid Receivable.
                    |--------------------------------------------------------------------------
                    */

                    $settlementPayment =
                        (float) (
                            $settlement->payment_amount
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Previous Consignment Receivable Payment
                    |--------------------------------------------------------------------------
                    |
                    | Only Posted Receivable payments.
                    |--------------------------------------------------------------------------
                    */

                    $previousReceivablePaid =
                        (float) (
                            $previousReceivablePayments[
                                $settlement->id
                            ] ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Outstanding
                    |--------------------------------------------------------------------------
                    |
                    | receivable_amount already represents:
                    |
                    | Grand Total - Settlement Payment
                    |
                    | Therefore only Posted Receivable payments
                    | are deducted here.
                    |--------------------------------------------------------------------------
                    */

                    $outstandingAmount =
                        max(
                            0,
                            (float)
                                $settlement->receivable_amount
                            -
                            $previousReceivablePaid
                        );


                    return [

                        'id' =>
                            $settlement->id,

                        'settlement_number' =>
                            $settlement
                                ->settlement_number,

                        'settlement_date' =>
                            $settlement
                                ->settlement_date,

                        'period_from' =>
                            $settlement
                                ->period_from,

                        'period_to' =>
                            $settlement
                                ->period_to,

                        'company_id' =>
                            $settlement
                                ->company_id,

                        'branch_id' =>
                            $settlement
                                ->branch_id,

                        'reseller_id' =>
                            $settlement
                                ->reseller_id,

                        'reseller' => [

                            'id' =>
                                $settlement
                                    ->reseller
                                    ?->id,

                            'code' =>
                                $settlement
                                    ->reseller
                                    ?->reseller_code,

                            'name' =>
                                $settlement
                                    ->reseller
                                    ?->name,

                        ],

                        /*
                        |--------------------------------------------------------------------------
                        | Settlement Amount
                        |--------------------------------------------------------------------------
                        */

                        'grand_total' =>
                            $settlement
                                ->grand_total,

                        /*
                        |--------------------------------------------------------------------------
                        | Payment made on Settlement
                        |--------------------------------------------------------------------------
                        */

                        'settlement_payment_amount' =>
                            $settlementPayment,

                        /*
                        |--------------------------------------------------------------------------
                        | Original Receivable
                        |--------------------------------------------------------------------------
                        */

                        'receivable_amount' =>
                            $settlement
                                ->receivable_amount,

                        /*
                        |--------------------------------------------------------------------------
                        | Previous Paid
                        |--------------------------------------------------------------------------
                        |
                        | Only previous Posted Receivable payments.
                        |--------------------------------------------------------------------------
                        */

                        'previous_paid_amount' =>
                            $previousReceivablePaid,

                        /*
                        |--------------------------------------------------------------------------
                        | Outstanding
                        |--------------------------------------------------------------------------
                        */

                        'outstanding_amount' =>
                            $outstandingAmount,

                        'payment_status' =>
                            $settlement
                                ->payment_status,

                        'status' =>
                            $settlement
                                ->status,

                    ];

                }
            )
            ->filter(
                function ($settlement) {

                    return
                        (float)
                            $settlement[
                                'outstanding_amount'
                            ] > 0;

                }
            )
            ->values();


    /*
    |--------------------------------------------------------------------------
    | Return
    |--------------------------------------------------------------------------
    */

    return [

        'companies' =>
            $companies,

        'branches' =>
            $branches,

        'resellers' =>
            $resellers,

        'paymentAccounts' =>
            $paymentAccounts,

        'settlements' =>
            $settlementData,

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
            'Resellers/ConsignmentReceivable/Create',

            array_merge(

                [

                    'title' =>
                        'Create Consignment Receivable',

                    'previewNumber' =>
                        $this
                            ->codeGeneratorService
                            ->preview(
                                'consignment_receivable'
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
        ConsignmentReceivableHeader $consignmentReceivable
    ) {

        abort_if(
            ! in_array(
                $consignmentReceivable->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected consignment receivable can be edited.'
        );

        $consignmentReceivable->load([

            'company',
            'branch',
            'reseller',
            'paymentAccount',

            'details.settlement',

        ]);

        return Inertia::render(
            'Resellers/ConsignmentReceivable/Edit',

            array_merge(

                [

                    'title' =>
                        'Edit Consignment Receivable',

                    'consignmentReceivable' =>
                        $consignmentReceivable,

                ],

                $this->formData()

            )
        );
    }
public function store(StoreConsignmentReceivableRequest $request)
{
    $data = $request->validated();

    $branch = Branch::findOrFail($data['branch_id']);

    $data['company_id'] = $branch->company_id;

    $this->consignmentReceivableService->create($data);

    return redirect()
        ->route('consignment-receivables.index')
        ->with('success', 'Consignment receivable created successfully.');
}
    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        ConsignmentReceivableHeader $consignmentReceivable
    ) {

        $consignmentReceivable->load([

            'company',
            'branch',
            'reseller',
            'paymentAccount',

            'creator',
            'updater',
            'submitter',
            'approver',
            'poster',
            'canceller',
            'rejector',

            'details.settlement',

            'activities.performer',

        ]);

        return Inertia::render(
            'Resellers/ConsignmentReceivable/Show',

            [

                'receivable' =>
                    $consignmentReceivable,

            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateConsignmentReceivableRequest $request,
        ConsignmentReceivableHeader $consignmentReceivable
    ) {

        abort_if(
            ! in_array(
                $consignmentReceivable->status,
                [
                    'Draft',
                    'Rejected',
                ],
                true
            ),
            422,
            'Only Draft or Rejected consignment receivable can be updated.'
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
            ->consignmentReceivableService
            ->update(
                $consignmentReceivable,
                $data
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment receivable updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        ConsignmentReceivableHeader $consignmentReceivable
    ) {

        $this
            ->consignmentReceivableService
            ->submit(
                $consignmentReceivable
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment receivable submitted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        ConsignmentReceivableHeader $consignmentReceivable
    ) {

        $this
            ->consignmentReceivableService
            ->approve(
                $consignmentReceivable
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment receivable approved successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        RejectConsignmentReceivableRequest $request,
        ConsignmentReceivableHeader $consignmentReceivable
    ) {

        $this
            ->consignmentReceivableService
            ->reject(
                $consignmentReceivable,
                $request
                    ->validated()['reason']
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment receivable rejected successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        ConsignmentReceivableHeader $consignmentReceivable
    ) {

        $this
            ->consignmentReceivableService
            ->post(
                $consignmentReceivable
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Consignment receivable posted successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        ConsignmentReceivableHeader $consignmentReceivable
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
            $consignmentReceivable->status,
            [
                'Approved',
            ],
            true
        )) {

            return back()->withErrors([

                'status' =>
                    'Consignment Receivable cannot be cancelled in its current status.',

            ]);
        }

        $this
            ->consignmentReceivableService
            ->cancel(
                $consignmentReceivable,
                $validated['reason']
            );

        return back()
            ->with(
                'success',
                'Consignment receivable cancelled successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

    public function showData(
        ConsignmentReceivableHeader $consignmentReceivable
    ) {

        $consignmentReceivable->load([

            'company',
            'branch',
            'reseller',
            'paymentAccount',

            'creator',
            'updater',
            'submitter',
            'approver',
            'poster',
            'canceller',
            'rejector',

            'details.settlement',

            'activities.performer',

        ]);

        return response()->json([

            'data' =>
                $consignmentReceivable,

        ]);
    }
    public function print(
    ConsignmentReceivableHeader $consignmentReceivable
) {
    $consignmentReceivable->load([
        'company',
        'branch',
        'reseller',
        'paymentAccount',
        'creator',
        'poster',
        'details.settlement',
    ]);

    return view(
        'print.Reseller.Reports.consignment-receivable',
        [
            'receivable' => $consignmentReceivable,
        ]
    );
}
}