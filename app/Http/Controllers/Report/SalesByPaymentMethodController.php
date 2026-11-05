<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Branch;
use App\Services\Report\SalesByPaymentMethodService;
use App\Exports\Report\SalesByPaymentMethodExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class SalesByPaymentMethodController extends Controller
{
    protected SalesByPaymentMethodService $salesByPaymentMethodService;

    public function __construct(
        SalesByPaymentMethodService $salesByPaymentMethodService
    ) {
        $this->salesByPaymentMethodService = $salesByPaymentMethodService;
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

        abort_if(
            empty($selectedIds),
            422,
            'No accessible branch selected.'
        );

        $invalidIds = array_diff(
            $selectedIds,
            $accessibleIds
        );

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
    | Available Payment Methods
    |--------------------------------------------------------------------------
    |
    | Retrieve payment methods dynamically from posted POS payments
    | and posted Consignment receivable payments.
    |
    | The available methods are restricted to the user's company
    | and accessible branches.
    |
    */

    /*
|--------------------------------------------------------------------------
| Available Payment Methods
|--------------------------------------------------------------------------
*/

private function getAvailablePaymentMethods(
    int $companyId,
    array $branchIds
): array {

    /*
    |--------------------------------------------------------------------------
    | Retrieve POS Enum Values
    |--------------------------------------------------------------------------
    */

    $column = DB::select(
        "SHOW COLUMNS FROM pos_sale_payments LIKE 'payment_method'"
    );

    $posMethods = collect();

    if (!empty($column)) {

        $type = $column[0]->Type ?? '';

        if (preg_match('/^enum\((.*)\)$/i', $type, $matches)) {

            $posMethods = collect(
                str_getcsv($matches[1], ',', "'", '\\')
            );

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Retrieve Consignment Payment Methods
    |--------------------------------------------------------------------------
    */

    $consignmentMethods = DB::table(
        'consignment_receivable_headers'
    )
        ->where('company_id', $companyId)
        ->whereIn('branch_id', $branchIds)
        ->where('status', 'Posted')
        ->whereNull('deleted_at')
        ->whereNotNull('payment_method')
        ->distinct()
        ->pluck('payment_method');

    /*
    |--------------------------------------------------------------------------
    | Merge And Format
    |--------------------------------------------------------------------------
    */

    $labels = [
        'cash' => 'Cash',
        'qris' => 'QRIS',
        'debit_card' => 'Debit Card',
        'credit_card' => 'Credit Card',
        'transfer' => 'Transfer',
        'e_wallet' => 'E-Wallet',
        'ewallet' => 'E-Wallet',
    ];

    return $posMethods
        ->merge($consignmentMethods)
        ->map(fn ($method) => strtolower(trim($method)))
        ->filter()
        ->unique()
        ->map(function ($method) use ($labels) {

            return [
                'value' => $method,

                'label' => $labels[$method]
                    ?? ucwords(str_replace('_', ' ', $method)),
            ];

        })
        ->sortBy('label')
        ->values()
        ->all();
}

    /*
    |--------------------------------------------------------------------------
    | Parameters
    |--------------------------------------------------------------------------
    */

    public function parameters()
    {
        $user = Auth::user();

        abort_unless(
            $user->can('reports.view'),
            403
        );

        abort_unless(
            $user->company_id,
            403
        );

        $branches = $this->getAccessibleBranches($user);

        abort_if(
            $branches->isEmpty(),
            403,
            'No accessible branch found.'
        );

        $branchIds = $branches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $canViewAllBranches = $user->can(
            'reports.view-all-branches'
        );

        $currentBranchId = (int) session(
            'current_branch_id'
        );

        if (!$canViewAllBranches) {

            abort_unless(
                in_array($currentBranchId, $branchIds, true),
                403,
                'Current branch is not accessible.'
            );

            $defaultBranchIds = [$currentBranchId];

        } else {

            $defaultBranchIds = $branchIds;
        }

        return Inertia::render(
            'Reports/Parameters/SalesByPaymentMethod',
            [
                'branches' => $branches,

                'paymentMethods' => $this->getAvailablePaymentMethods(
                    (int) $user->company_id,
                    $branchIds
                ),

                'canViewAllBranches' => $canViewAllBranches,

                'filters' => [

                    'date_from' => now()
                        ->startOfMonth()
                        ->toDateString(),

                    'date_to' => now()
                        ->toDateString(),

                    'branch_ids' => $defaultBranchIds,

                    'payment_methods' => [],

                    'source' => 'all',
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

        abort_unless(
            $user->can('reports.view'),
            403
        );

        $companyId = $user->company_id;

        abort_unless(
            $companyId,
            403
        );

        $accessibleBranches = $this->getAccessibleBranches($user);

        $accessibleBranchIds = $accessibleBranches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        abort_if(
            empty($accessibleBranchIds),
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Available Payment Methods
        |--------------------------------------------------------------------------
        */

        $availableMethods = collect(
            $this->getAvailablePaymentMethods(
                (int) $companyId,
                $accessibleBranchIds
            )
        )
            ->pluck('value')
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'date_from' => [
                'required',
                'date',
            ],

            'date_to' => [
                'required',
                'date',
                'after_or_equal:date_from',
            ],

            'branch_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'branch_ids.*' => [
                'required',
                'integer',
                'distinct',
            ],

            'payment_methods' => [
                'present',
                'array',
            ],

            'payment_methods.*' => [
                'required',
                'string',
                'distinct',
                Rule::in($availableMethods),
            ],

            'source' => [
                'required',
                'in:all,pos,consignment',
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Resolve Branch Selection
        |--------------------------------------------------------------------------
        */

        $branchScope = $this->resolveReportBranches(
            $user,
            $validated['branch_ids']
        );

        /*
        |--------------------------------------------------------------------------
        | Normalize Payment Methods
        |--------------------------------------------------------------------------
        */

        $paymentMethods = array_values(array_unique(
            $validated['payment_methods']
        ));

        $paymentMethodLabels = collect(
            $this->getAvailablePaymentMethods(
                (int) $companyId,
                $accessibleBranchIds
            )
        )
            ->whereIn('value', $paymentMethods)
            ->pluck('label')
            ->values()
            ->all();

        $paymentMethodLabel = empty($paymentMethods)
            ? 'All Payment Methods'
            : implode(', ', $paymentMethodLabels);

        /*
        |--------------------------------------------------------------------------
        | Normalize Filters
        |--------------------------------------------------------------------------
        */

        $filters = [

            'company_id' => (int) $companyId,

            'branch_ids' => $branchScope['selected_ids'],

            'branch_label' => $branchScope['branch_label'],

            'payment_methods' => $paymentMethods,

            'payment_method_labels' => $paymentMethodLabels,

            'payment_method_label' => $paymentMethodLabel,

            'date_from' => $validated['date_from'],

            'date_to' => $validated['date_to'],

            'source' => $validated['source'],

        ];

        /*
        |--------------------------------------------------------------------------
        | Store Filters
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'sales_by_payment_method_filters_' . $user->id,
            $filters
        );

        return redirect()->route(
            'reports.sales.by-payment-method.results'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Results
    |--------------------------------------------------------------------------
    */

    public function results(Request $request)
    {
        $user = Auth::user();

        abort_unless(
            $user->can('reports.view'),
            403
        );

        abort_unless(
            $user->company_id,
            403
        );

        $filters = $request->session()->get(
            'sales_by_payment_method_filters_' . $user->id
        );

        abort_unless(
            $filters,
            404,
            'Sales by Payment Method filters not found.'
        );

        abort_unless(
            (int) ($filters['company_id'] ?? 0) ===
            (int) $user->company_id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Revalidate Branch Access
        |--------------------------------------------------------------------------
        */

        $branchScope = $this->resolveReportBranches(
            $user,
            $filters['branch_ids'] ?? []
        );

        /*
        |--------------------------------------------------------------------------
        | Generate Report
        |--------------------------------------------------------------------------
        */

        $report = $this->salesByPaymentMethodService->generate(
            $filters,
            true
        );

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company = $user->company()
            ->select(
                'id',
                'company_name'
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Render Results
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Reports/Results/SalesByPaymentMethod',
            [
                'report' => $report,

                'branches' => $branchScope['branches'],

                'branchLabel' => $branchScope['branch_label'],

                'company' => $company
                    ? $company->only([
                        'id',
                        'company_name',
                    ])
                    : null,

                'generatedAt' => Carbon::now()
                    ->format('d M Y H:i'),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Get Export Data
    |--------------------------------------------------------------------------
    */

    private function getExportData(
        Request $request
    ): array {

        $user = Auth::user();

        abort_unless(
            $user->can('reports.view'),
            403
        );

        abort_unless(
            $user->can('reports.export'),
            403
        );

        $companyId = $user->company_id;

        abort_unless(
            $companyId,
            403
        );

        $reportFilters = $request->session()->get(
            'sales_by_payment_method_filters_' . $user->id
        );

        abort_unless(
            $reportFilters,
            404
        );

        abort_unless(
            (int) ($reportFilters['company_id'] ?? 0) ===
            (int) $companyId,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | Revalidate Branch Access
        |--------------------------------------------------------------------------
        */

        $branchScope = $this->resolveReportBranches(
            $user,
            $reportFilters['branch_ids'] ?? []
        );

        /*
        |--------------------------------------------------------------------------
        | Generate Complete Report
        |--------------------------------------------------------------------------
        */

        $report = $this->salesByPaymentMethodService->generate(
            $reportFilters,
            false
        );

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        $company = $user->company()->first();

        /*
        |--------------------------------------------------------------------------
        | Return Export Data
        |--------------------------------------------------------------------------
        */

        return [
            'report' => $report,

            'branches' => $branchScope['branches'],

            'branchLabel' => $branchScope['branch_label'],

            'company' => $company,

            'filters' => $reportFilters,

            'generatedAt' => Carbon::now()
                ->format('d M Y H:i'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Print Sales by Payment Method
    |--------------------------------------------------------------------------
    */

    public function print(Request $request)
    {
        $data = $this->getExportData($request);

        return view(
            'print.Report.sales-by-payment-method',
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF Sales by Payment Method
    |--------------------------------------------------------------------------
    */

    public function exportPdf(Request $request)
    {
        $data = $this->getExportData($request);

        return Pdf::loadView(
            'pdf.Report.sales-by-payment-method',
            $data
        )
            ->setPaper('a4', 'landscape')
            ->download('sales-by-payment-method.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Excel Sales by Payment Method
    |--------------------------------------------------------------------------
    */

    public function exportExcel(Request $request)
    {
        $data = $this->getExportData($request);

        return Excel::download(
            new SalesByPaymentMethodExport($data),
            'sales-by-payment-method.xlsx'
        );
    }
}