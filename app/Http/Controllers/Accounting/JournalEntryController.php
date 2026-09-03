<?php

namespace App\Http\Controllers\Accounting;
use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\ChartOfAccount;
use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalEntry;
use App\Models\MasterData\Branch;
use App\Services\Accounting\JournalEntryService;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\Accounting\JournalEntry\StoreJournalEntryRequest;
use App\Http\Requests\Accounting\JournalEntry\UpdateJournalEntryRequest;
class JournalEntryController extends Controller
{
    public function __construct(
        protected JournalEntryService $journalEntryService,
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
        $query = JournalEntry::query()
    ->with([
        'branch',
        'accountingJournal',
        'lines.account',
    ])

    ->withSum(
        'lines as total_debit',
        'debit'
    )

    ->withSum(
        'lines as total_credit',
        'credit'
    )

    ->when(
        $request->filled('search'),
        function ($query) use ($request) {

            $search = $request->search;

            $query->where(function ($query) use ($search) {

                $query->where(
                    'code',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'reference',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'description',
                    'like',
                    "%{$search}%"
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
                    'accountingJournal',
                    function ($journal) use ($search) {

                        $journal->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );

                    }
                );

            });

        }
    )

              
            ->when(
                $request->filled('branch_id'),
                function ($query) use ($request) {

                    $query->where(
                        'branch_id',
                        $request->branch_id
                    );

                }
            )

            ->when(
                $request->filled('accounting_journal_id'),
                function ($query) use ($request) {

                    $query->where(
                        'accounting_journal_id',
                        $request->accounting_journal_id
                    );

                }
            )

            ->when(
                $request->filled('status'),
                function ($query) use ($request) {

                    $query->where(
                        'status',
                        $request->status
                    );

                }
            )

            ->when(
                $request->filled('entry_date'),
                function ($query) use ($request) {

                    $query->where(
                        'entry_date',
                        $request->entry_date
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $journalEntries = $query
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
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statisticsQuery = clone $query;

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

            'posted' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Posted'
                    )
                    ->count(),

            'reversed' =>
                (clone $statisticsQuery)
                    ->where(
                        'status',
                        'Reversed'
                    )
                    ->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Form Data
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Accounting/JournalEntry/Index',
            [

                'title' =>
                    'Journal Entry',

                'journalEntries' =>
                    $journalEntries,

                'statistics' =>
                    $statistics,

                'previewNumber' =>
                    $this->codeGeneratorService
                        ->preview('journal'),

                'filters' =>
                    $request->only([
                        'search',
                        'branch_id',
                        'accounting_journal_id',
                        'status',
                        'entry_date',
                        'per_page',
                    ]),

               ...$this->formData(
                    $request->integer('branch_id')
                ),

            ]
        );
    }

private function formData(
    ?int $branchId = null
): array {

    /*
    |--------------------------------------------------------------------------
    | Branches
    |--------------------------------------------------------------------------
    */

    $branches = Branch::query()
        ->orderBy('name')
        ->get([
            'id',
            'company_id',
            'name',
        ])
        ->map(fn ($branch) => [

            'id' =>
                $branch->id,

            'company_id' =>
                $branch->company_id,

            'label' =>
                $branch->name,

        ])
        ->values();


    /*
    |--------------------------------------------------------------------------
    | Resolve Company
    |--------------------------------------------------------------------------
    */

    $companyId = null;

 if ($branchId) {

    $branch = $branches->firstWhere(
        'id',
        $branchId
    );

    $companyId = $branch['company_id'] ?? null;

}

    /*
    |--------------------------------------------------------------------------
    | Accounting Journals
    |--------------------------------------------------------------------------
    */

    $accountingJournals =
        AccountingJournal::query()
            ->when(
                $companyId,
                fn ($query) =>
                    $query->where(
                        'company_id',
                        $companyId
                    )
            )
            ->active()
            ->orderBy('code')
            ->get([
                'id',
                'company_id',
                'code',
                'name',
                'type',
            ])
            ->map(fn ($journal) => [

                'id' =>
                    $journal->id,

                'company_id' =>
                    $journal->company_id,

                'code' =>
                    $journal->code,

                'name' =>
                    $journal->name,

                'type' =>
                    $journal->type,

                'label' =>
                    implode(
                        ' - ',
                        array_filter([
                            $journal->code,
                            $journal->name,
                        ])
                    ),

            ])
            ->values();


    /*
    |--------------------------------------------------------------------------
    | Fiscal Years
    |--------------------------------------------------------------------------
    */

    $fiscalYears =
        FiscalYear::query()
            ->when(
                $companyId,
                fn ($query) =>
                    $query->where(
                        'company_id',
                        $companyId
                    )
            )
            ->open()
            ->orderByDesc('year')
            ->get([
                'id',
                'company_id',
                'year',
                'start_date',
                'end_date',
            ])
            ->map(fn ($fiscalYear) => [

                'id' =>
                    $fiscalYear->id,

                'company_id' =>
                    $fiscalYear->company_id,

                'year' =>
                    $fiscalYear->year,

                'start_date' =>
                    $fiscalYear->start_date,

                'end_date' =>
                    $fiscalYear->end_date,

                'label' =>
                    (string) $fiscalYear->year,

            ])
            ->values();


    /*
    |--------------------------------------------------------------------------
    | Accounting Periods
    |--------------------------------------------------------------------------
    */

    $accountingPeriods =
        AccountingPeriod::query()
            ->when(
                $companyId,
                fn ($query) =>
                    $query->where(
                        'company_id',
                        $companyId
                    )
            )
            ->open()
            ->orderBy('fiscal_year_id')
            ->orderBy('period_number')
            ->get([
                'id',
                'company_id',
                'fiscal_year_id',
                'period_number',
                'name',
                'start_date',
                'end_date',
            ])
            ->map(fn ($period) => [

                'id' =>
                    $period->id,

                'company_id' =>
                    $period->company_id,

                'fiscal_year_id' =>
                    $period->fiscal_year_id,

                'period_number' =>
                    $period->period_number,

                'name' =>
                    $period->name,

                'start_date' =>
                    $period->start_date,

                'end_date' =>
                    $period->end_date,

                'label' =>
                    $period->name,

            ])
            ->values();


    /*
    |--------------------------------------------------------------------------
    | Chart Of Accounts
    |--------------------------------------------------------------------------
    */

    $chartOfAccounts =
        ChartOfAccount::query()
            ->when(
                $companyId,
                fn ($query) =>
                    $query->where(
                        'company_id',
                        $companyId
                    )
            )
            ->active()
            ->posting()
            ->orderBy('code')
            ->get([
                'id',
                'company_id',
                'code',
                'name',
            ])
            ->map(fn ($account) => [

                'id' =>
                    $account->id,

                'company_id' =>
                    $account->company_id,

                'code' =>
                    $account->code,

                'name' =>
                    $account->name,

                'label' =>
                    implode(
                        ' - ',
                        array_filter([
                            $account->code,
                            $account->name,
                        ])
                    ),

            ])
            ->values();


    return [

        'branches' =>
            $branches,

        'accountingJournals' =>
            $accountingJournals,

        'fiscalYears' =>
            $fiscalYears,

        'accountingPeriods' =>
            $accountingPeriods,

        'chartOfAccounts' =>
            $chartOfAccounts,

    ];
}

    public function store(
        StoreJournalEntryRequest $request
    ) {
        $data = $request->validated();

        $branch = Branch::findOrFail(
            $data['branch_id']
        );

        $data['company_id'] =
            $branch->company_id;

        $this->journalEntryService
            ->create($data);

        return redirect()
            ->back()
            ->with(
                'success',
                'Journal entry created successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        JournalEntry $journalEntry
    ) {

        $this->journalEntryService
            ->post($journalEntry);


        return redirect()
            ->back()
            ->with(
                'success',
                'Journal entry posted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        JournalEntry $journalEntry
    ) {

        $journalEntry->load([

            'company',
            'branch',

            'accountingJournal',
            'fiscalYear',
            'accountingPeriod',

            'lines.account',

        ]);


        return Inertia::render(
            'Accounting/JournalEntry/Show',
            [
                'journalEntry' =>
                    $journalEntry,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Data
    |--------------------------------------------------------------------------
    */

    public function showData(
        JournalEntry $journalEntry
    ) {

        $journalEntry->load([

            'company',
            'branch',

           'accountingJournal',
            'fiscalYear',
            'accountingPeriod',


            'lines.account',

            'createdBy',
            'updatedBy',
            'postedBy',
            'deletedBy',
            'activities.performer',
        ]);


        return response()->json([

            'data' =>
                $journalEntry,

        ]);
    }
    /*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

public function update(
    UpdateJournalEntryRequest $request,
    JournalEntry $journalEntry
) {

    $data = $request->validated();


    /*
    |--------------------------------------------------------------------------
    | Update Journal Entry
    |--------------------------------------------------------------------------
    */

    $this->journalEntryService
        ->update(
            $journalEntry,
            $data
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Journal entry updated successfully.'
        );
}
}