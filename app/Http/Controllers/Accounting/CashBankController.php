<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Models\Accounting\CashBank;
use Illuminate\Http\Request;
use Inertia\Inertia;
 use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Services\CodeGeneratorService;
class CashBankController extends Controller
{
   public function index(Request $request)
{
   $query = CashBank::query()

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

    );
      
    $summaryQuery = clone $query;
                $sortable = [

                'code',

                'name',

                'type',

                'current_balance',

                'status',

                'created_at',

            ];

            $sort = in_array(

                $request->sort,

                $sortable

            )

                ? $request->sort

                : 'created_at';

            $direction =

                $request->direction === 'asc'

                    ? 'asc'

                    : 'desc';
                    
    $cashBanks = $query

    ->orderBy(

        $sort,

        $direction

    )

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
                
                'sort' => $sort,

                'direction' => $direction,

            ],

            'summary' => [

            'total_accounts' => $summaryQuery->count(),

            'cash_accounts' => (

                clone $summaryQuery

            )

            ->where(

                'type',

                'Cash'

            )

            ->count(),

            'bank_accounts' => (

                clone $summaryQuery

            )

            ->where(

                'type',

                'Bank'

            )

            ->count(),

            'current_balance' => (

                clone $summaryQuery

            )

            ->sum(

                'current_balance'

            ),

        ],

        ]

    );
}

private function formData(): array
{
    return [

        'companies' => Company::query()
            ->where('status', true)
            ->orderBy('company_name')
            ->get([
                'id',
                'company_name',
            ]),

        'branches' => Branch::query()
            ->where('status', true)
            ->orderBy('name')
            ->get([
                'id',
                'company_id',
                'name',
            ]),

        /*
        |--------------------------------------------------------------------------
        | COA
        |--------------------------------------------------------------------------
        |
        | Nanti kita isi ketika modul COA selesai.
        |
        */

        'coaAccounts' => [],

    ];
}
public function create()
{
    return Inertia::render(

        'Accounting/CashBank/Create',

        array_merge(

            $this->formData(),

            [

                'generatedCode' => CodeGeneratorService::cashBank(),

            ]

        )

    );
}

  public function edit(CashBank $cashBank)
{
    return Inertia::render(

        'Accounting/CashBank/Edit',

        array_merge(

            $this->formData(),

            [

                'cashBank' => $cashBank,

            ]

        )

    );
}
    public function store(Request $request)
{
    $validated = $request->validate(

    [

        'code' => 'required|unique:cash_banks,code',

        'name' => 'required|max:150',

        'type' => 'required|in:Cash,Bank',

        'company_id' => 'required|exists:companies,id',

        'branch_id' => 'required|exists:branches,id',

        'bank_name' => 'required_if:type,Bank',

        'bank_branch' => 'nullable',

        'account_number' => 'required_if:type,Bank',

        'account_holder' => 'required_if:type,Bank',

        'opening_balance' => 'required|numeric|min:0',

        'status' => 'required|boolean',

        'description' => 'nullable',

    ],

    [

        'company_id.required' => 'Company is required.',

        'branch_id.required' => 'Branch is required.',

        'name.required' => 'Account Name is required.',

        'bank_name.required_if' => 'Bank Name is required.',

        'account_number.required_if' => 'Account Number is required.',

        'account_holder.required_if' => 'Account Holder is required.',

    ]

);

    $validated['code'] =

        CodeGeneratorService::cashBank();

    $validated['current_balance'] =

        $validated['opening_balance'];

    $validated['created_by'] =

        auth()->id();

    CashBank::create(

        $validated

    );

    if ($request->boolean('create_another')) {

    return redirect()

        ->route('cash-banks.create')

        ->with(

            'success',

            'Cash / Bank created successfully.'

        );

}
    return redirect()

        ->route(

            'cash-banks.index'

        )

        ->with(

            'success',

            'Cash / Bank created successfully.'

        );
}

public function show(
    CashBank $cashBank
)
{
    $cashBank->load(

        'company',

        'branch',

        'creator',

        'updater'

    );

    return Inertia::render(

        'Accounting/CashBank/Show',

        [

            'cashBank' => $cashBank,

        ]

    );
}



    public function update(
    Request $request,
    CashBank $cashBank
)

{
    $validated = $request->validate([

        'code' => [

            'required',

            Rule::unique(
                'cash_banks',
                'code'
            )->ignore(
                $cashBank->id
            ),

        ],

        'name' => 'required|max:150',

        'type' => 'required|in:Cash,Bank',

        'company_id' => 'required|exists:companies,id',

        'branch_id' => 'required|exists:branches,id',

        'bank_name' => 'required_if:type,Bank',

        'bank_branch' => 'nullable',

        'account_number' => 'required_if:type,Bank',

        'account_holder' => 'required_if:type,Bank',

        'opening_balance' => 'required|numeric|min:0',

        'status' => 'required|boolean',

        'description' => 'nullable',

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

    public function destroy(
    CashBank $cashBank
)
{
    /*
    |--------------------------------------------------------------------------
    | Check Existing Transaction
    |--------------------------------------------------------------------------
    |
    | TODO :
    | Saat modul Accounting selesai,
    | cek apakah Cash Bank sudah dipakai pada :
    |
    | - Journal Entry
    | - Supplier Payment
    | - Customer Payment
    | - Cash Receipt
    | - Cash Payment
    |
    */

    $hasTransaction = false;

    if (

        $hasTransaction

    ) {

        $cashBank->update([

            'status' => false,

            'updated_by' => auth()->id(),

        ]);

        return redirect()

            ->route(

                'cash-banks.index'

            )

            ->with(

                'warning',

                'Cash / Bank has been used in transactions and has been set to Inactive.'

            );

    }

    $cashBank->delete();

    return redirect()

        ->route(

            'cash-banks.index'

        )

        ->with(

            'success',

            'Cash / Bank deleted successfully.'

        );
}
public function bulkDelete(Request $request)
{
    CashBank::whereIn(
        'id',
        $request->ids
    )->delete();

    return back()->with(
        'success',
        'Selected Cash Bank deleted successfully.'
    );
}
public function bulkActivate(Request $request)
{
    CashBank::whereIn(
        'id',
        $request->ids
    )->update([
        'is_active' => true,
    ]);

    return back()->with(
        'success',
        'Selected Cash Bank activated successfully.'
    );
}

public function bulkDeactivate(Request $request)
{
    CashBank::whereIn(
        'id',
        $request->ids
    )->update([
        'is_active' => false,
    ]);

    return back()->with(
        'success',
        'Selected Cash Bank deactivated successfully.'
    );
}
public function duplicate(CashBank $cashBank)
{
    return Inertia::render(

        'Accounting/CashBank/Create',

        array_merge(

            $this->formData(),

            [

                'generatedCode' => CodeGeneratorService::cashBank(),

                'duplicate' => [

                    'company_id'      => $cashBank->company_id,

                    'branch_id'       => $cashBank->branch_id,

                    'name'            => $cashBank->name . ' (Copy)',

                    'type'            => $cashBank->type,

                    'bank_name'       => $cashBank->bank_name,

                    'bank_branch'     => $cashBank->bank_branch,

                    'account_number'  => $cashBank->account_number,

                    'account_holder'  => $cashBank->account_holder,

                    'coa_id'          => $cashBank->coa_id,

                    'description'     => $cashBank->description,

                    'status'          => true,

                ],

            ]

        )

    );
}
public function print(
    CashBank $cashBank
)
{
    $cashBank->load(
        'company',

        'branch'

    );

    return Inertia::render(

        'Accounting/CashBank/Print',

        [

            'cashBank' => $cashBank,

        ]

    );
}
public function export(CashBank $cashBank)
{
    return Excel::download(

        new CashBankExport($cashBank),

        'CashBank_'.$cashBank->code.'.xlsx'

    );
}
}