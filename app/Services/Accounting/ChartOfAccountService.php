<?php


namespace App\Services\Accounting;

use App\Models\Accounting\AccountCategory;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\AccountType;
use App\Models\MasterData\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\MasterData\Branch;
class ChartOfAccountService
{
    

public function indexData(Request $request): array
{
    $query = ChartOfAccount::query()
        ->with([
            'company',
            'accountCategory',
            'parent',
        ])
        ->when(
            $request->search,
            function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('code', 'like', "%{$request->search}%")
                        ->orWhere('name', 'like', "%{$request->search}%");
                });
            }
        )
        ->when(
            $request->filled('status'),
            fn ($query) => $query->where('status', $request->status)
        )

        ->when(
                $request->filled('account_category_id'),
                fn ($query) => $query->where(
                    'account_category_id',
                    $request->account_category_id
                )
            )

            ->when(
                $request->filled('account_type_id'),
                function ($query) use ($request) {

                    $query->whereHas(
                        'accountCategory',
                        function ($q) use ($request) {

                            $q->where(
                                'account_type_id',
                                $request->account_type_id
                            );

                        }
                    );

                }
            );

    $summaryQuery = clone $query;

    $sortable = [
        'code',
        'name',
        'status',
        'created_at',
    ];

    $sort = in_array($request->sort, $sortable)
        ? $request->sort
        : 'code';

    $direction = $request->direction === 'desc'
        ? 'desc'
        : 'asc';

   $perPage = (int) $request->input('per_page', 50);

    $perPage = in_array($perPage, [10, 20, 50, 100])
        ? $perPage
        : 10;

    $chartOfAccounts = $query
        ->orderBy($sort, $direction)
        ->paginate($perPage)
        ->withQueryString();

    return [

        'chartOfAccounts' => $chartOfAccounts,

        'accountCategories' => AccountCategory::query()
            ->select('id', 'name')
            ->where('status', true)
            ->orderBy('name')
            ->get(),

        'accountTypes' => AccountType::query()
        ->select('id', 'name')
        ->where('status', true)
        ->orderBy('name')
        ->get(),
        
        'filters' => [

            'search' => $request->search,

            'status' => $request->status,

             'account_type_id' => $request->account_type_id,

            'account_category_id' => $request->account_category_id,

            'sort' => $sort,

            'direction' => $direction,

            'per_page' => $perPage,

        ],

        'summary' => [

            'total_accounts' => $summaryQuery->count(),

            'active_accounts' => (clone $summaryQuery)
                ->where('status', true)
                ->count(),

            'inactive_accounts' => (clone $summaryQuery)
                ->where('status', false)
                ->count(),

                'header_accounts' => (clone $summaryQuery)
                ->where('is_header', true)
                ->count(),

            'posting_accounts' => (clone $summaryQuery)
                ->where('is_posting', true)
                ->count(),

        ],

    ];
}

public function formData(): array
{
    return [

        'companies' => Company::query()
            ->where('status', true)
            ->orderBy('company_name')
            ->get([
                'id',
                'company_name',
            ]),

        'accountCategories' => AccountCategory::query()
            ->where('status', true)
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
            ]),

        'parentAccounts' => ChartOfAccount::query()
            ->where('status', true)
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
            ]),

    ];
}
public function store(array $data): ChartOfAccount
{
    return DB::transaction(function () use ($data) {

        $parent = null;
        $level = 1;

        if (!empty($data['parent_id'])) {

            $parent = ChartOfAccount::findOrFail($data['parent_id']);

            $level = $parent->level + 1;

            // Generate kode otomatis untuk child account
            $data['code'] = $this->generateCode(
                $data['parent_id'] ?? null
            );

        } else {

            // Sementara untuk header account,
            // gunakan kode yang dikirim dari form.
            // Nanti kita buat generateHeaderCode().
            $data['code'] = $data['code'];

        }

        $data['level'] = $level;

        $data['created_by'] = auth()->id();

        return ChartOfAccount::create($data);
    });
}
public function update(
    ChartOfAccount $chartOfAccount,
    array $data
): ChartOfAccount
{
    return DB::transaction(function () use ($chartOfAccount, $data) {

        $parent = null;
        $level = 1;

        if (!empty($data['parent_id'])) {

            $parent = ChartOfAccount::findOrFail($data['parent_id']);

            $level = $parent->level + 1;
        }

        $data['level'] = $level;

        if (
            $chartOfAccount->parent_id != ($data['parent_id'] ?? null)
        ) {
            $data['code'] = $this->generateCode(
                $data['parent_id'] ?? null
            );
        }

        $data['updated_by'] = auth()->id();

        $chartOfAccount->update($data);

        return $chartOfAccount->fresh([
            'company',
            'accountCategory',
            'parent',
        ]);
    });
}

public function destroy(
    ChartOfAccount $chartOfAccount
): void
{
    /*
    |--------------------------------------------------------------------------
    | Check Existing Transaction
    |--------------------------------------------------------------------------
    |
    | TODO :
    | Saat modul Accounting selesai,
    | cek apakah COA sudah dipakai pada :
    |
    | - Journal Entry
    | - Ledger
    | - Trial Balance
    | - Financial Statement
    |
    */

    $hasTransaction = false;

    if ($hasTransaction) {

        $chartOfAccount->update([

            'status' => false,

            'updated_by' => auth()->id(),

        ]);

        return;
    }

    $chartOfAccount->update([

        'deleted_by' => auth()->id(),

    ]);

    $chartOfAccount->delete();
}
public function bulkDelete(array $ids): void
{
    ChartOfAccount::whereIn('id', $ids)
        ->update([
            'deleted_by' => auth()->id(),
        ]);

    ChartOfAccount::whereIn('id', $ids)->delete();
}

public function bulkActivate(array $ids): void
{
    ChartOfAccount::whereIn('id', $ids)
        ->update([
            'status' => true,
            'updated_by' => auth()->id(),
        ]);
}

public function bulkDeactivate(array $ids): void
{
    ChartOfAccount::whereIn('id', $ids)
        ->update([
            'status' => false,
            'updated_by' => auth()->id(),
        ]);
}
public function duplicate(
    ChartOfAccount $chartOfAccount
): array
{
    return array_merge(

        $this->formData(),

        [

            'duplicate' => [

                'company_id' => $chartOfAccount->company_id,

                'parent_id' => $chartOfAccount->parent_id,

                'account_category_id' => $chartOfAccount->account_category_id,

                'code' => null,

                'name' => $chartOfAccount->name . ' (Copy)',

                'normal_balance' => $chartOfAccount->normal_balance,

                'is_header' => $chartOfAccount->is_header,

                'is_posting' => $chartOfAccount->is_posting,

                'opening_balance' => $chartOfAccount->opening_balance,

                'status' => true,

                'description' => $chartOfAccount->description,

            ],

        ]

    );
}

public function printData(
    ChartOfAccount $chartOfAccount
): array
{
    return [

        'chartOfAccount' => $chartOfAccount->load(

            'company',

            'accountCategory',

            'parent',

            'creator',

            'updater'

        ),

    ];
}

public function export(
    ChartOfAccount $chartOfAccount
)
{
    /*
    |--------------------------------------------------------------------------
    | TODO
    |--------------------------------------------------------------------------
    |
    | Export Excel / PDF
    |
    | return Excel::download(
    |     new ChartOfAccountExport($chartOfAccount),
    |     'ChartOfAccount_'.$chartOfAccount->code.'.xlsx'
    | );
    |
    */

    return null;
}
public function generateCode(?int $parentId): string
{
    /*
    |--------------------------------------------------------------------------
    | Child Account
    |--------------------------------------------------------------------------
    */
    if ($parentId) {

        $parent = ChartOfAccount::findOrFail($parentId);

        $lastChild = ChartOfAccount::where('parent_id', $parent->id)
            ->orderByDesc('code')
            ->first();

        if (!$lastChild) {
            return (string) (((int) $parent->code) + 1);
        }

        return (string) (((int) $lastChild->code) + 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Root Header Account
    |--------------------------------------------------------------------------
    */

    $lastRoot = ChartOfAccount::whereNull('parent_id')
        ->orderByDesc('code')
        ->first();

    if (!$lastRoot) {
        return '100000';
    }

    return (string) (((int) $lastRoot->code) + 100000);
}
}