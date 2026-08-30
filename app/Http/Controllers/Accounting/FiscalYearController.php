<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreFiscalYearRequest;
use App\Models\Accounting\FiscalYear;
use App\Services\Accounting\FiscalYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FiscalYearController extends Controller
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

   /*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

public function index(
    Request $request
): Response {

    $query =
        FiscalYear::query()

            ->when(

                $request->search,

                function ($query) use ($request) {

                    $query->where(

                        function ($q) use ($request) {

                            $q->where(
                                'year',
                                'like',
                                '%' . $request->search . '%'
                            )
                            ->orWhere(
                                'description',
                                'like',
                                '%' . $request->search . '%'
                            );

                        }

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

            );


    /*
    |--------------------------------------------------------------------------
    | Summary Query
    |--------------------------------------------------------------------------
    */

    $summaryQuery =
        clone $query;


    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */

    $sortable = [

        'year',

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
            : 'year';


    $direction =
        $request->direction === 'asc'
            ? 'asc'
            : 'desc';


    /*
    |--------------------------------------------------------------------------
    | Fiscal Years
    |--------------------------------------------------------------------------
    */

    $fiscalYears =
        $query

            ->with([
                'periods',
            ])

            ->withCount([
                'periods',
            ])

            ->orderBy(
                $sort,
                $direction
            )

            ->paginate(10)

            ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return Inertia::render(

        'Accounting/FiscalYear/Index',

        [

            'fiscalYears' =>
                $fiscalYears,


            'filters' => [

                'search' =>
                    $request->search,

                'status' =>
                    $request->status,

                'sort' =>
                    $sort,

                'direction' =>
                    $direction,

            ],


            'summary' => [

                'total_fiscal_years' =>
                    $summaryQuery->count(),

                'open_fiscal_years' =>

                    (
                        clone $summaryQuery
                    )
                    ->where(
                        'status',
                        'Open'
                    )
                    ->count(),

                'closed_fiscal_years' =>

                    (
                        clone $summaryQuery
                    )
                    ->where(
                        'status',
                        'Closed'
                    )
                    ->count(),

                'current_year' =>

                    (
                        clone $summaryQuery
                    )
                    ->where(
                        'status',
                        'Open'
                    )
                    ->max(
                        'year'
                    ),

            ],

        ]

    );

}
public function create(): Response
{
    return Inertia::render(
        'Accounting/FiscalYear/Create',
        $this->fiscalYearService->formData()
    );
}
   /*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

public function store(
    StoreFiscalYearRequest $request
): RedirectResponse {

    $data =
        $request->validated();


    $data['created_by'] =
        $request->user()->id;

    $data['updated_by'] =
        $request->user()->id;


    $this
        ->fiscalYearService
        ->createFiscalYear(
            $data
        );


    return redirect()
        ->back()
        ->with(
            'success',
            'Fiscal year created successfully.'
        );

}
    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        FiscalYear $fiscalYear
    ): Response {

        $fiscalYear->load([
            'company',
            'periods.closer',
            'creator',
            'updater',
            'closer',
        ]);


        return Inertia::render(
            'Accounting/FiscalYear/Show',
            [
                'fiscalYear' =>
                    $fiscalYear,
            ]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Close
    |--------------------------------------------------------------------------
    */

    public function close(
        FiscalYear $fiscalYear,
        Request $request
    ): RedirectResponse {

        $this
            ->fiscalYearService
            ->closeFiscalYear(
                $fiscalYear,
                $request->user()->id
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Fiscal year closed successfully.'
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Reopen
    |--------------------------------------------------------------------------
    */

    public function reopen(
        FiscalYear $fiscalYear,
        Request $request
    ): RedirectResponse {

        $this
            ->fiscalYearService
            ->reopenFiscalYear(
                $fiscalYear,
                $request->user()->id
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Fiscal year reopened successfully.'
            );

    }

}