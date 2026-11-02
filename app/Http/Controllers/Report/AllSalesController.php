<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Customer;
use App\Models\MasterData\Company;
use App\Models\Reseller\Reseller;
use App\Models\User;
use App\Services\Report\AllSalesService;
use App\Exports\Report\AllSalesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class AllSalesController extends Controller
{
    protected AllSalesService $allSalesService;

    public function __construct(AllSalesService $allSalesService)
    {
        $this->allSalesService = $allSalesService;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessible Branches
    |--------------------------------------------------------------------------
    */

    private function getAccessibleBranches($user)
    {
        if ($user->can('reports.view-all-branches')) {
            return Branch::query()
                ->where('company_id', $user->company_id)
                ->active()
                ->orderBy('name')
                ->get();
        }

        return $user->branches()
            ->where('branches.company_id', $user->company_id)
            ->where('branches.is_active', true)
            ->orderBy('branches.name')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Report Branches
    |--------------------------------------------------------------------------
    */

    private function resolveReportBranches(
        $user,
        array $selectedBranchIds
    ): array {

        $accessibleBranches = $this->getAccessibleBranches($user);

        $accessibleIds = $accessibleBranches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $selectedIds = array_values(array_unique(array_map(
            'intval',
            $selectedBranchIds
        )));

        abort_if(empty($selectedIds), 422, 'No accessible branch selected.');

        $invalidIds = array_diff($selectedIds, $accessibleIds);

        abort_unless(
            empty($invalidIds),
            403,
            'You do not have access to one or more selected branches.'
        );

        $branches = $accessibleBranches
            ->whereIn('id', $selectedIds)
            ->values();

        abort_if(
            $branches->count() !== count($selectedIds),
            403,
            'Selected branch is not available.'
        );

        $allBranchesSelected =
            count($selectedIds) === count($accessibleIds) &&
            empty(array_diff($accessibleIds, $selectedIds));

        $branchLabel = $allBranchesSelected
            ? 'All Branches'
            : $branches->pluck('name')->implode(', ');

        return [
            'branches' => $branches,
            'accessible_ids' => $accessibleIds,
            'selected_ids' => $selectedIds,
            'branch_label' => $branchLabel,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Parameters
    |--------------------------------------------------------------------------
    */

    public function parameters()
    {
        $user = Auth::user();

        abort_unless($user->can('reports.view'), 403);

        abort_unless($user->company_id, 403);

        $branches = $this->getAccessibleBranches($user);

        abort_if($branches->isEmpty(), 403, 'No accessible branch found.');

        $branchIds = $branches->pluck('id')->map(fn ($id) => (int) $id)->all();

        $canViewAllBranches = $user->can('reports.view-all-branches');

        $currentBranchId = (int) session('current_branch_id');

        $defaultBranchId = null;

        if (!$canViewAllBranches) {
            abort_unless(
                in_array($currentBranchId, $branchIds, true),
                403,
                'Current branch is not accessible.'
            );

            $defaultBranchId = $currentBranchId;
        }

        $dateFrom = now()->startOfMonth()->toDateString();

        $dateTo = now()->toDateString();

        $customers = Customer::query()
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'customer_code', 'name']);

        $resellers = Reseller::query()
            ->where('company_id', $user->company_id)
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'reseller_code', 'name']);

        $cashiers = User::query()
            ->join(
                'cashier_sessions',
                'cashier_sessions.user_id',
                '=',
                'users.id'
            )
            ->where('cashier_sessions.company_id', $user->company_id)
            ->whereIn('cashier_sessions.branch_id', $branchIds)
            ->select('users.id', 'users.name')
            ->distinct()
            ->orderBy('users.name')
            ->get();

        $paymentMethods = [
            ['value' => 'cash', 'label' => 'Cash'],
            ['value' => 'qris', 'label' => 'QRIS'],
            ['value' => 'debit_card', 'label' => 'Debit Card'],
            ['value' => 'transfer', 'label' => 'Bank Transfer'],
            ['value' => 'e_wallet', 'label' => 'E-Wallet'],
        ];

        return Inertia::render(
            'Reports/Parameters/AllSales',
            [
                'branches' => $branches,
                'customers' => $customers,
                'resellers' => $resellers,
                'cashiers' => $cashiers,
                'paymentMethods' => $paymentMethods,

                'permissions' => [
                    'viewAllBranches' => $canViewAllBranches,
                ],

                'filters' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'branch_id' => $defaultBranchId,
                    'source' => 'all',
                    'customer_id' => null,
                    'reseller_id' => null,
                    'cashier_id' => null,
                    'payment_method' => null,
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Report
    |--------------------------------------------------------------------------
    */

    public function generate(Request $request)
    {
        $user = Auth::user();

        abort_unless($user->can('reports.view'), 403);

        $companyId = $user->company_id;

        abort_unless($companyId, 403);

        $accessibleBranches = $this->getAccessibleBranches($user);

        $accessibleBranchIds = $accessibleBranches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        abort_if(empty($accessibleBranchIds), 403);

        $validated = $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],

            'branch_id' => ['nullable', 'integer'],

            'source' => [
                'required',
                'in:all,pos,consignment',
            ],

            'customer_id' => ['nullable', 'integer'],
            'reseller_id' => ['nullable', 'integer'],
            'cashier_id' => ['nullable', 'integer'],

            'payment_method' => [
                'nullable',
                'in:cash,qris,debit_card,transfer,e_wallet',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Resolve Branch Selection
        |--------------------------------------------------------------------------
        */

        $branchId = $validated['branch_id'] ?? null;

        if ($branchId) {
            abort_unless(
                in_array((int) $branchId, $accessibleBranchIds, true),
                403,
                'Selected branch is not accessible.'
            );

            $selectedBranchIds = [(int) $branchId];
        } else {
            abort_unless(
                $user->can('reports.view-all-branches'),
                403,
                'You are not allowed to view all branches.'
            );

            $selectedBranchIds = $accessibleBranchIds;
        }

        /*
        |--------------------------------------------------------------------------
        | Customer Validation
        |--------------------------------------------------------------------------
        */

        $customerId = null;

        if (
            in_array($validated['source'], ['all', 'pos'], true) &&
            !empty($validated['customer_id'])
        ) {
            $customerId = Customer::query()
                ->where('status', true)
                ->whereKey($validated['customer_id'])
                ->value('id');

            abort_unless($customerId, 422, 'Invalid customer selected.');
        }

        /*
        |--------------------------------------------------------------------------
        | Reseller Validation
        |--------------------------------------------------------------------------
        */

        $resellerId = null;

        if (
            in_array($validated['source'], ['all', 'consignment'], true) &&
            !empty($validated['reseller_id'])
        ) {
            $resellerId = Reseller::query()
                ->where('company_id', $companyId)
                ->where('status', true)
                ->whereKey($validated['reseller_id'])
                ->value('id');

            abort_unless($resellerId, 422, 'Invalid reseller selected.');
        }

        /*
        |--------------------------------------------------------------------------
        | Cashier Validation
        |--------------------------------------------------------------------------
        */

        $cashierId = null;

        if (
            in_array($validated['source'], ['all', 'pos'], true) &&
            !empty($validated['cashier_id'])
        ) {
            $cashierId = DB::table('cashier_sessions')
                ->where('company_id', $companyId)
                ->whereIn('branch_id', $selectedBranchIds)
                ->where('user_id', $validated['cashier_id'])
                ->value('user_id');

            abort_unless($cashierId, 422, 'Invalid cashier selected.');
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Filters
        |--------------------------------------------------------------------------
        */

        $filters = [
            'company_id' => (int) $companyId,

            'branch_ids' => $selectedBranchIds,

            'date_from' => $validated['date_from'],

            'date_to' => $validated['date_to'],

            'source' => $validated['source'],

            'customer_id' => $customerId,

            'reseller_id' => $resellerId,

            'cashier_id' => $cashierId,

            'payment_method' =>
                in_array($validated['source'], ['all', 'pos'], true)
                    ? ($validated['payment_method'] ?? null)
                    : null,
        ];

        /*
        |--------------------------------------------------------------------------
        | Store Filters
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'all_sales_filters_' . $user->id,
            $filters
        );

        return redirect()->route('reports.sales.all.results');
    }


    /*
|--------------------------------------------------------------------------
| Results
|--------------------------------------------------------------------------
*/

public function results(Request $request)
{
    $user = Auth::user();

    abort_unless($user->can('reports.view'), 403);

    abort_unless($user->company_id, 403);

    $filters = $request->session()->get(
        'all_sales_filters_' . $user->id
    );

    abort_unless($filters, 404, 'All Sales filters not found.');

    abort_unless(
        (int) ($filters['company_id'] ?? 0) === (int) $user->company_id,
        403
    );

    $branchScope = $this->resolveReportBranches(
        $user,
        $filters['branch_ids'] ?? []
    );

    $report = $this->allSalesService->generate(
        $filters,
        true
    );

    $company = $user->company()
        ->select('id', 'company_name')
        ->first();

    return Inertia::render(
        'Reports/Results/AllSales',
        [
            'report' => $report,

            'branches' => $branchScope['branches'],

            'branchLabel' => $branchScope['branch_label'],

            'company' => $company
                ? $company->only(['id', 'company_name'])
                : null,

            'generatedAt' => Carbon::now()->format('d M Y H:i'),
        ]
    );
}

    /*
    |--------------------------------------------------------------------------
    | Get Export Data
    |--------------------------------------------------------------------------
    */

    private function getExportData(Request $request): array
    {
        $user = Auth::user();

        abort_unless($user->can('reports.view'), 403);

        abort_unless($user->can('reports.export'), 403);

        $companyId = $user->company_id;

        abort_unless($companyId, 403);

        $reportFilters = $request->session()->get(
            'all_sales_filters_' . $user->id
        );

        abort_unless($reportFilters, 404);

        abort_unless(
            (int) ($reportFilters['company_id'] ?? 0) === (int) $companyId,
            403
        );

        $branchScope = $this->resolveReportBranches(
            $user,
            $reportFilters['branch_ids'] ?? []
        );

        $report = $this->allSalesService->generate(
            $reportFilters,
            false
        );

        $company = $user->company()->first();

        return [
            'report' => $report,
            'branches' => $branchScope['branches'],
            'branchLabel' => $branchScope['branch_label'],
            'company' => $company,
            'filters' => $reportFilters,
            'generatedAt' => Carbon::now()->format('d M Y H:i'),
        ];
    }


   /*
|--------------------------------------------------------------------------
| Print All Sales
|--------------------------------------------------------------------------
*/

public function print(Request $request)
{
    $data = $this->getExportData($request);

    $query = $data['report']['transactions'];

    $transactions = $query->paginate(100);

    $data['report']['transactions'] = $transactions;

    return view(
        'print.Report.all-sales',
        $data
    );
}

    /*
    |--------------------------------------------------------------------------
    | PDF All Sales
    |--------------------------------------------------------------------------
    */

    public function exportPdf(Request $request)
    {
        $data = $this->getExportData($request);

        $query = $data['report']['transactions'];

        $totalTransactions = (clone $query)->count();

        abort_if(
            $totalTransactions > 500,
            422,
            'This report contains more than 500 transactions. Please narrow the date range or export the complete report to Excel.'
        );

        $transactions = $query->get();

        $data['report']['transactions'] = $transactions;

        return Pdf::loadView(
            'pdf.Report.all-sales',
            $data
        )
            ->setPaper('a4', 'landscape')
            ->download('all-sales.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Excel All Sales
    |--------------------------------------------------------------------------
    */

    public function exportExcel(Request $request)
    {
        $data = $this->getExportData($request);

        return Excel::download(
            new AllSalesExport($data),
            'all-sales.xlsx'
        );
    }
}