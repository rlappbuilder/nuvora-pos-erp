<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use App\Models\Accounting\CashBank;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashBankController extends Controller
{
   public function index(Request $request)
{
    $cashBanks = CashBank::query()

        ->when(

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

                            'bank_name',

                            'like',

                            '%' . $request->search . '%'

                        )

                        ->orWhere(

                            'account_number',

                            'like',

                            '%' . $request->search . '%'

                        );

                    }

                );

            }

        )

        ->when(

            $request->type,

            function ($query) use ($request) {

                $query->where(

                    'type',

                    $request->type

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

        ->latest()

        ->paginate(10)

        ->withQueryString();

    return Inertia::render(

        'Accounting/CashBank/Index',

        [

            'cashBanks' => $cashBanks,

            'filters' => [

                'search' => $request->search,

                'type' => $request->type,

                'status' => $request->status,

            ],

            'summary' => [

                'total_accounts' => CashBank::count(),

                'cash_accounts' => CashBank::where(

                    'type',

                    'Cash'

                )->count(),

                'bank_accounts' => CashBank::where(

                    'type',

                    'Bank'

                )->count(),

                'current_balance' => CashBank::sum(

                    'opening_balance'

                ),

            ],

        ]

    );
}

    public function create()
    {
        return Inertia::render(

            'CashBank/Create'

        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([

            'code' => 'required|unique:cash_banks',

            'name' => 'required',

            'type' => 'required',

            'bank_name' => 'nullable',

            'account_number' => 'nullable',

            'account_holder' => 'nullable',

            'opening_balance' => 'required|numeric',

            'remarks' => 'nullable',

        ]);

        $validated['current_balance'] =

            $validated['opening_balance'];

        $validated['created_by'] =

            auth()->id();

        CashBank::create(

            $validated

        );

        return redirect()

            ->route(

                'cash-banks.index'

            )

            ->with(

                'success',

                'Cash / Bank created successfully.'

            );
    }

    public function show(CashBank $cashBank)
    {
        return Inertia::render(

            'CashBank/Show',

            [

                'cashBank' => $cashBank,

            ]

        );
    }

    public function edit(CashBank $cashBank)
    {
        return Inertia::render(

            'CashBank/Edit',

            [

                'cashBank' => $cashBank,

            ]

        );
    }

    public function update(Request $request, CashBank $cashBank)
    {
        $validated = $request->validate([

            'code' =>

                'required|unique:cash_banks,code,'

                .

                $cashBank->id,

            'name' => 'required',

            'type' => 'required',

            'bank_name' => 'nullable',

            'account_number' => 'nullable',

            'account_holder' => 'nullable',

            'remarks' => 'nullable',

            'is_active' => 'boolean',

        ]);

        $validated['updated_by'] =

            auth()->id();

        $cashBank->update(

            $validated

        );

        return redirect()

            ->route(

                'cash-banks.index'

            )

            ->with(

                'success',

                'Cash / Bank updated successfully.'

            );
    }

    public function destroy(CashBank $cashBank)
    {
        $cashBank->delete();

        return back()

            ->with(

                'success',

                'Cash / Bank deleted successfully.'

            );
    }
}