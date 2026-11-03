<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Branch;
use App\Services\Report\ConsignmentSalesService;
use App\Exports\Report\ConsignmentSalesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ConsignmentSalesController extends Controller
{
    protected ConsignmentSalesService $consignmentSalesService;

    public function __construct(
        ConsignmentSalesService $consignmentSalesService
    ) {
        $this->consignmentSalesService = $consignmentSalesService;
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
    | Resolve Report Reseller
    |--------------------------------------------------------------------------
    */

    private function resolveReportReseller(
        int $companyId,
        $resellerId
    ): array {

        if ($resellerId === null || $resellerId === '') {

            return [
                'selected_id' => null,
                'reseller' => null,
                'reseller_name' => 'All Resellers',
            ];
        }

        $reseller = DB::table('resellers')
            ->where('id', $resellerId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereNull('deleted_at')
            ->first([
                'id',
                'reseller_code',
                'name',
            ]);

        abort_unless(
            $reseller,
            422,
            'Selected reseller is invalid or inactive.'
        );

        return [
            'selected_id' => (int) $reseller->id,
            'reseller' => $reseller,
            'reseller_name' => $reseller->name,
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

        /*
        |--------------------------------------------------------------------------
        | Resellers
        |--------------------------------------------------------------------------
        */

        $resellers = DB::table('resellers')
            ->where('company_id', $user->company_id)
            ->where('status', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get([
                'id',
                'reseller_code',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Render Parameters
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Reports/Parameters/ConsignmentSales',
            [
                'branches' => $branches,

                'resellers' => $resellers,

                'canViewAllBranches' => $canViewAllBranches,

                'filters' => [

                    'date_from' => now()
                        ->startOfMonth()
                        ->toDateString(),

                    'date_to' => now()
                        ->toDateString(),

                    'branch_ids' => $defaultBranchIds,

                    'reseller_id' => null,
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

            'reseller_id' => [
                'nullable',
                'integer',
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
        | Resolve Reseller Selection
        |--------------------------------------------------------------------------
        */

        $resellerScope = $this->resolveReportReseller(
            (int) $companyId,
            $validated['reseller_id'] ?? null
        );

        /*
        |--------------------------------------------------------------------------
        | Normalize Filters
        |--------------------------------------------------------------------------
        */

        $filters = [

            'company_id' =>
                (int) $companyId,

            'branch_ids' =>
                $branchScope['selected_ids'],

            'date_from' =>
                $validated['date_from'],

            'date_to' =>
                $validated['date_to'],

            'reseller_id' =>
                $resellerScope['selected_id'],

            'reseller_name' =>
                $resellerScope['reseller_name'],
        ];

        /*
        |--------------------------------------------------------------------------
        | Store Filters
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'consignment_sales_filters_' . $user->id,
            $filters
        );

        return redirect()->route(
            'reports.sales.consignment-sales.results'
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
            'consignment_sales_filters_' . $user->id
        );

        abort_unless(
            $filters,
            404,
            'Consignment Sales filters not found.'
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
        | Revalidate Reseller Selection
        |--------------------------------------------------------------------------
        */

        $resellerScope = $this->resolveReportReseller(
            (int) $user->company_id,
            $filters['reseller_id'] ?? null
        );

        $filters['reseller_id'] =
            $resellerScope['selected_id'];

        $filters['reseller_name'] =
            $resellerScope['reseller_name'];

        /*
        |--------------------------------------------------------------------------
        | Generate Report
        |--------------------------------------------------------------------------
        */

        $report = $this->consignmentSalesService->generate(
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
            'Reports/Results/ConsignmentSales',
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
            'consignment_sales_filters_' . $user->id
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
        | Revalidate Reseller Selection
        |--------------------------------------------------------------------------
        */

        $resellerScope = $this->resolveReportReseller(
            (int) $companyId,
            $reportFilters['reseller_id'] ?? null
        );

        $reportFilters['reseller_id'] =
            $resellerScope['selected_id'];

        $reportFilters['reseller_name'] =
            $resellerScope['reseller_name'];

        /*
        |--------------------------------------------------------------------------
        | Generate Complete Report
        |--------------------------------------------------------------------------
        */

        $report = $this->consignmentSalesService->generate(
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

            'report' =>
                $report,

            'branches' =>
                $branchScope['branches'],

            'branchLabel' =>
                $branchScope['branch_label'],

            'company' =>
                $company,

            'filters' =>
                $reportFilters,

            'generatedAt' =>
                Carbon::now()->format('d M Y H:i'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Print Consignment Sales
    |--------------------------------------------------------------------------
    */

    public function print(Request $request)
    {
        $data = $this->getExportData($request);

        return view(
            'print.Report.consignment-sales',
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF Consignment Sales
    |--------------------------------------------------------------------------
    */

    public function exportPdf(Request $request)
    {
        $data = $this->getExportData($request);

        return Pdf::loadView(
            'pdf.Report.consignment-sales',
            $data
        )
            ->setPaper('a4', 'landscape')
            ->download('consignment-sales.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Excel Consignment Sales
    |--------------------------------------------------------------------------
    */

    public function exportExcel(Request $request)
    {
        $data = $this->getExportData($request);

        return Excel::download(
            new ConsignmentSalesExport($data),
            'consignment-sales.xlsx'
        );
    }
}