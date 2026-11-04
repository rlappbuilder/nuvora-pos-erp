<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Branch;
use App\Services\Report\SalesByBranchService;
use App\Exports\Report\SalesByBranchExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class SalesByBranchController extends Controller
{
    protected SalesByBranchService $salesByBranchService;

    public function __construct(
        SalesByBranchService $salesByBranchService
    ) {
        $this->salesByBranchService = $salesByBranchService;
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
        | Render Parameters
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Reports/Parameters/SalesByBranch',
            [
                'branches' => $branches,

                'canViewAllBranches' => $canViewAllBranches,

                'filters' => [

                    'date_from' => now()
                        ->startOfMonth()
                        ->toDateString(),

                    'date_to' => now()
                        ->toDateString(),

                    'branch_ids' => $defaultBranchIds,

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
        | Normalize Filters
        |--------------------------------------------------------------------------
        */

        $filters = [

            'company_id' => (int) $companyId,

            'branch_ids' => $branchScope['selected_ids'],

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
            'sales_by_branch_filters_' . $user->id,
            $filters
        );

        return redirect()->route(
            'reports.sales.by-branch.results'
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
            'sales_by_branch_filters_' . $user->id
        );

        abort_unless(
            $filters,
            404,
            'Sales by Branch filters not found.'
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

        $report = $this->salesByBranchService->generate(
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
            'Reports/Results/SalesByBranch',
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
            'sales_by_branch_filters_' . $user->id
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

        $report = $this->salesByBranchService->generate(
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
    | Print Sales by Branch
    |--------------------------------------------------------------------------
    */

    public function print(Request $request)
    {
        $data = $this->getExportData($request);

        return view(
            'print.Report.sales-by-branch',
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF Sales by Branch
    |--------------------------------------------------------------------------
    */

    public function exportPdf(Request $request)
    {
        $data = $this->getExportData($request);

        return Pdf::loadView(
            'pdf.Report.sales-by-branch',
            $data
        )
            ->setPaper('a4', 'landscape')
            ->download('sales-by-branch.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Excel Sales by Branch
    |--------------------------------------------------------------------------
    */

    public function exportExcel(Request $request)
    {
        $data = $this->getExportData($request);

        return Excel::download(
            new SalesByBranchExport($data),
            'sales-by-branch.xlsx'
        );
    }
}