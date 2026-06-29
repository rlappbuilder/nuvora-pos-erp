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

                function (

                    $query,

                    $search

                ) {

                    $query->where(

                        'code',

                        'like',

                        "%{$search}%"

                    )

                    ->orWhere(

                        'name',

                        'like',

                        "%{$search}%"

                    )

                    ->orWhere(

                        'bank_name',

                        'like',

                        "%{$search}%"

                    );

                }

            )

            ->latest()

            ->paginate(10)

            ->withQueryString();

        return Inertia::render(

            'CashBank/Index',

            [

                'cashBanks' => $cashBanks,

                'filters' => [

                    'search' => $request->search,

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