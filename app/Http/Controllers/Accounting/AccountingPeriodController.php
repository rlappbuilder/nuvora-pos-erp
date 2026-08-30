<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountingPeriod;
use App\Services\Accounting\FiscalYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Accounting\FiscalYear;
class AccountingPeriodController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        protected FiscalYearService $fiscalYearService
    ) {
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        $query =
            AccountingPeriod::query()
                ->with([
                    'company',
                    'fiscalYear',
                ]);

 
        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $query->when(

            $request->search,

            function ($query) use ($request) {

                $query->where(

                    function ($q) use ($request) {

                        $q->where(
                            'name',
                            'like',
                            '%' . $request->search . '%'
                        )
                        ->orWhere(
                            'period_number',
                            'like',
                            '%' . $request->search . '%'
                        );

                    }

                );

            }

        );


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        $query->when(

            $request->filled('status'),

            function ($query) use ($request) {

                $query->where(
                    'status',
                    $request->status
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
        | Sorting
        |--------------------------------------------------------------------------
        */

        $sortable = [

            'period_number',

            'name',

            'start_date',

            'end_date',

            'status',

            'created_at',

        ];


        $sort =
            in_array(
                $request->sort,
                $sortable
            )
                ? $request->sort
                : 'period_number';


        $direction =
            $request->direction === 'desc'
                ? 'desc'
                : 'asc';


        /*
        |--------------------------------------------------------------------------
        | Periods
        |--------------------------------------------------------------------------
        */

        $periods =
            $query

                ->orderBy(
                    $sort,
                    $direction
                )

                ->paginate(
                    $request->per_page ?? 12
                )

                ->withQueryString();

           /*
            |--------------------------------------------------------------------------
            | Fiscal Years Filter
            |--------------------------------------------------------------------------
            */

            $fiscalYears =
                FiscalYear::query()
                    ->orderByDesc('year')
                    ->get([
                        'id',
                        'year',
                    ]);
        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

       return Inertia::render(

    'Accounting/AccountingPeriod/Index',

    [

        'periods' =>
            $periods,

        'fiscalYears' =>
            $fiscalYears,

        'filters' => [

            'search' =>
                $request->search,

            'status' =>
                $request->status,

            'fiscal_year_id' =>
                $request->fiscal_year_id,

            'sort' =>
                $sort,

            'direction' =>
                $direction,

            'per_page' =>
                $request->per_page ?? 12,

        ],

    ]

);

    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        AccountingPeriod $accountingPeriod
    ): Response {

        $accountingPeriod->load([
            'company',
            'fiscalYear',
            'creator',
            'updater',
            'closer',
        ]);


        return Inertia::render(

            'Accounting/AccountingPeriod/Show',

            [

                'period' =>
                    $accountingPeriod,

            ]

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Close
    |--------------------------------------------------------------------------
    */

    public function close(
        AccountingPeriod $accountingPeriod,
        Request $request
    ): RedirectResponse {

        $this
            ->fiscalYearService
            ->closePeriod(
                $accountingPeriod,
                $request->user()->id
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Accounting period closed successfully.'
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Reopen
    |--------------------------------------------------------------------------
    */

    public function reopen(
        AccountingPeriod $accountingPeriod,
        Request $request
    ): RedirectResponse {

        $this
            ->fiscalYearService
            ->reopenPeriod(
                $accountingPeriod,
                $request->user()->id
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Accounting period reopened successfully.'
            );

    }

}