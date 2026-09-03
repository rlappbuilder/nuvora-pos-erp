<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\AccountingJournal\StoreAccountingJournalRequest;
use App\Http\Requests\Accounting\AccountingJournal\UpdateAccountingJournalRequest;
use App\Models\Accounting\AccountingJournal;
use App\Services\Accounting\AccountingJournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountingJournalController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */

    public function __construct(
        protected AccountingJournalService $accountingJournalService
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
            AccountingJournal::query()
                ->with([
                    'company',
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
                            'code',
                            'like',
                            '%' . $request->search . '%'
                        )
                        ->orWhere(
                            'name',
                            'like',
                            '%' . $request->search . '%'
                        )
                        ->orWhere(
                            'type',
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
                    'is_active',
                    $request->status
                );

            }

        );


        /*
        |--------------------------------------------------------------------------
        | Type
        |--------------------------------------------------------------------------
        */

        $query->when(

            $request->filled('type'),

            function ($query) use ($request) {

                $query->where(
                    'type',
                    $request->type
                );

            }

        );


        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $sortable = [

            'code',

            'name',

            'type',

            'is_active',

            'created_at',

        ];


        $sort =
            in_array(
                $request->sort,
                $sortable
            )
                ? $request->sort
                : 'code';


        $direction =
            $request->direction === 'desc'
                ? 'desc'
                : 'asc';


        /*
        |--------------------------------------------------------------------------
        | Journals
        |--------------------------------------------------------------------------
        */

        $journals =
            $query

                ->orderBy(
                    $sort,
                    $direction
                )

                ->paginate(
                    $request->per_page ?? 10
                )

                ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Stats
        |--------------------------------------------------------------------------
        */

        $stats = [

            'total' =>
                AccountingJournal::count(),

            'active' =>
                AccountingJournal::active()->count(),

            'inactive' =>
                AccountingJournal::inactive()->count(),

        ];


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(

            'Accounting/AccountingJournal/Index',

            [

                'journals' =>
                    $journals,

                'stats' =>
                    $stats,

                'filters' => [

                    'search' =>
                        $request->search,

                    'status' =>
                        $request->status,

                    'type' =>
                        $request->type,

                    'sort' =>
                        $sort,

                    'direction' =>
                        $direction,

                    'per_page' =>
                        $request->per_page ?? 10,

                ],

            ]

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): Response
    {
        return Inertia::render(

            'Accounting/AccountingJournal/Create',

            $this
                ->accountingJournalService
                ->formData()

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreAccountingJournalRequest $request
    ): RedirectResponse {

        $data =
            $request->validated();


        $data['created_by'] =
            $request->user()->id;

        $data['updated_by'] =
            $request->user()->id;


        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        $this
            ->accountingJournalService
            ->createAccountingJournal(
                $data
            );


        return redirect()
            ->route(
                'accounting-journals.index'
            )
            ->with(
                'success',
                'Accounting journal created successfully.'
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        AccountingJournal $accountingJournal
    ): Response {

        $accountingJournal->load([
            'company',
            'accounts.account',
            'creator',
            'updater',
            'deleter',
        ]);


        return Inertia::render(

            'Accounting/AccountingJournal/Show',

            [

                'journal' =>
                    $accountingJournal,

            ]

        );

    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */
public function edit(
    AccountingJournal $accountingJournal
): Response {

    $accountingJournal->load([
        'accounts.account',
    ]);


    return Inertia::render(

        'Accounting/AccountingJournal/Edit',

        [

            'journal' =>
                $accountingJournal,

            ...$this
                ->accountingJournalService
                ->formData(
                    $accountingJournal->company_id
                ),

        ]

    );

}
    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateAccountingJournalRequest $request,
        AccountingJournal $accountingJournal
    ): RedirectResponse {

        $data =
            $request->validated();


        $data['updated_by'] =
            $request->user()->id;


        $this
            ->accountingJournalService
            ->updateAccountingJournal(
                $accountingJournal,
                $data
            );


        return redirect()
            ->route(
                'accounting-journals.index'
            )
            ->with(
                'success',
                'Accounting journal updated successfully.'
            );

    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        AccountingJournal $accountingJournal
    ): RedirectResponse {

        $this
            ->accountingJournalService
            ->deleteAccountingJournal(
                $accountingJournal,
                $request->user()->id
            );


        return redirect()
            ->back()
            ->with(
                'success',
                'Accounting journal deleted successfully.'
            );

    }

}