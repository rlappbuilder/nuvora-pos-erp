<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\GeneralLedger;
use App\Models\MasterData\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GeneralLedgerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = GeneralLedger::query()
            ->with([
                'branch',
                'account',
                'accountingJournal',
                'fiscalYear',
                'accountingPeriod',
                'journalEntry',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('search'),
            function ($query) use ($request) {

                $search = $request->search;

                $query->where(function ($query) use ($search) {

                    $query->where(
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
                        'account',
                        function ($account) use ($search) {

                            $account->where(
                                'code',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'name',
                                'like',
                                "%{$search}%"
                            );

                        }
                    )

                    ->orWhereHas(
                        'journalEntry',
                        function ($journalEntry) use ($search) {

                            $journalEntry->where(
                                'code',
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
                        'accountingJournal',
                        function ($journal) use ($search) {

                            $journal->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )

                            ->orWhere(
                                'code',
                                'like',
                                "%{$search}%"
                            );

                        }
                    );

                });

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('branch_id'),
            function ($query) use ($request) {

                $query->where(
                    'branch_id',
                    $request->branch_id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Account
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('account_id'),
            function ($query) use ($request) {

                $query->where(
                    'account_id',
                    $request->account_id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Accounting Journal
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('accounting_journal_id'),
            function ($query) use ($request) {

                $query->where(
                    'accounting_journal_id',
                    $request->accounting_journal_id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Fiscal Year
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('fiscal_year_id'),
            function ($query) use ($request) {

                $query->where(
                    'fiscal_year_id',
                    $request->fiscal_year_id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Accounting Period
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('accounting_period_id'),
            function ($query) use ($request) {

                $query->where(
                    'accounting_period_id',
                    $request->accounting_period_id
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('date_from'),
            function ($query) use ($request) {

                $query->whereDate(
                    'entry_date',
                    '>=',
                    $request->date_from
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('date_to'),
            function ($query) use ($request) {

                $query->whereDate(
                    'entry_date',
                    '<=',
                    $request->date_to
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [

            'id',

            'entry_date',

            'debit',

            'credit',

            'running_balance',

        ];


        $sortBy =
            $request->input(
                'sort_by',
                'entry_date'
            );


        $sortDirection =
            $request->input(
                'sort_direction',
                'asc'
            );


        if (
            ! in_array(
                $sortBy,
                $allowedSorts,
                true
            )
        ) {

            $sortBy =
                'entry_date';

        }


        if (
            ! in_array(
                $sortDirection,
                ['asc', 'desc'],
                true
            )
        ) {

            $sortDirection =
                'asc';

        }


        $query
            ->orderBy(
                $sortBy,
                $sortDirection
            )
            ->orderBy(
                'id',
                'asc'
            );


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $generalLedgers =
            $query
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

        $statisticsQuery =
            clone $query;


        $statistics = [

            'total' =>
                (clone $statisticsQuery)
                    ->count(),

            'debit' =>
                (clone $statisticsQuery)
                    ->sum('debit'),

            'credit' =>
                (clone $statisticsQuery)
                    ->sum('credit'),

        ];


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Accounting/GeneralLedger/Index',
            [

                'title' =>
                    'General Ledger',

                'generalLedgers' =>
                    $generalLedgers,

                'statistics' =>
                    $statistics,

                'filters' =>
                    $request->only([

                        'search',

                        'branch_id',

                        'account_id',

                        'accounting_journal_id',

                        'fiscal_year_id',

                        'accounting_period_id',

                        'date_from',

                        'date_to',

                        'per_page',

                        'sort_by',

                        'sort_direction',

                    ]),

                ...$this->formData(
                    $request
                ),

            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    private function formData(
        Request $request
    ): array {

        $branchId =
            $request->integer(
                'branch_id'
            );


        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */

        $branches =
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
                ->map(
                    fn ($journal) => [

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

                    ]
                )
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
                ->orderByDesc('year')
                ->get([
                    'id',
                    'company_id',
                    'year',
                    'start_date',
                    'end_date',
                ])
                ->map(
                    fn ($fiscalYear) => [

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

                    ]
                )
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
                ->map(
                    fn ($period) => [

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

                    ]
                )
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
                ->map(
                    fn ($account) => [

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

                    ]
                )
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
}