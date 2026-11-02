<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Branch;
use App\Services\Report\SalesSummaryService;
use App\Exports\Report\SalesSummaryExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class SalesSummaryController extends Controller
{
    protected SalesSummaryService $salesSummaryService;

    public function __construct(
        SalesSummaryService $salesSummaryService
    ) {
        $this->salesSummaryService = $salesSummaryService;
    }

    /*
    |--------------------------------------------------------------------------
    | Parameters
    |--------------------------------------------------------------------------
    */

    public function parameters(Request $request)
    {
        $user = Auth::user();

        abort_unless($user->can('reports.view'), 403);

        $companyId = $user->company_id;

        abort_unless(
            $companyId,
            403,
            'Company context is not available.'
        );

        $canViewAllBranches = $user->can(
            'reports.view-all-branches'
        );

        /*
        |--------------------------------------------------------------------------
        | Accessible Branches
        |--------------------------------------------------------------------------
        */

        if ($canViewAllBranches) {
            $branches = Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'code',
                    'name',
                ]);
        } else {
            $branches = $user->branches()
                ->where('branches.company_id', $companyId)
                ->where('branches.is_active', true)
                ->orderBy('branches.name')
                ->get([
                    'branches.id',
                    'branches.code',
                    'branches.name',
                ]);
        }

        $branchIds = $branches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        abort_if(
            empty($branchIds),
            403,
            'No accessible branches found.'
        );

        /*
        |--------------------------------------------------------------------------
        | Default Branch
        |--------------------------------------------------------------------------
        */

        if ($canViewAllBranches) {
            $selectedBranchId = null;
        } else {
            $selectedBranchId = session('current_branch_id');

            abort_unless(
                $selectedBranchId &&
                in_array(
                    (int) $selectedBranchId,
                    $branchIds,
                    true
                ),
                403,
                'You do not have access to the current branch.'
            );

            $selectedBranchId = (int) $selectedBranchId;
        }

        /*
        |--------------------------------------------------------------------------
        | Default Date
        |--------------------------------------------------------------------------
        */

        $dateFrom = Carbon::now()
            ->startOfMonth()
            ->toDateString();

        $dateTo = Carbon::now()
            ->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Reports/Parameters/SalesSummary',
            [
                'branches' => $branches,

                'filters' => [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'branch_id' => $selectedBranchId,
                ],

                'permissions' => [
                    'viewAllBranches' => $canViewAllBranches,
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate
    |--------------------------------------------------------------------------
    */

    public function generate(Request $request)
    {
        $user = Auth::user();

        abort_unless($user->can('reports.view'), 403);

        $companyId = $user->company_id;

        abort_unless(
            $companyId,
            403,
            'Company context is not available.'
        );

        $canViewAllBranches = $user->can(
            'reports.view-all-branches'
        );

        /*
        |--------------------------------------------------------------------------
        | Accessible Branches
        |--------------------------------------------------------------------------
        */

        if ($canViewAllBranches) {
            $branches = Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
        } else {
            $branches = $user->branches()
                ->where('branches.company_id', $companyId)
                ->where('branches.is_active', true)
                ->pluck('branches.id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
        }

        abort_if(
            empty($branches),
            403,
            'No accessible branches found.'
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Filters
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

            'branch_id' => [
                'nullable',
                'integer',
            ],

            'cashier_id' => [
                'nullable',
                'integer',
            ],

            'customer_id' => [
                'nullable',
                'integer',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Resolve Branch Scope
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['branch_id'])) {
            $selectedBranchId = (int) $validated['branch_id'];

            abort_unless(
                in_array(
                    $selectedBranchId,
                    $branches,
                    true
                ),
                403,
                'You do not have access to this branch.'
            );

            $selectedBranchIds = [
                $selectedBranchId,
            ];
        } else {
            abort_unless(
                $canViewAllBranches,
                403,
                'Branch selection is required.'
            );

            $selectedBranchIds = $branches;
        }

        /*
        |--------------------------------------------------------------------------
        | Store Report Filters
        |--------------------------------------------------------------------------
        */

        $reportFilters = [
            'company_id' => $companyId,
            'branch_ids' => $selectedBranchIds,
            'date_from' => $validated['date_from'],
            'date_to' => $validated['date_to'],
            'cashier_id' => $validated['cashier_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
        ];

        /*
        |--------------------------------------------------------------------------
        | Save Filters To Session
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'sales_summary_filters_' . $user->id,
            $reportFilters
        );

        /*
        |--------------------------------------------------------------------------
        | Redirect To Results
        |--------------------------------------------------------------------------
        */

        return redirect()->route(
            'reports.sales.summary.results'
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

        abort_unless($user->can('reports.view'), 403);

        $companyId = $user->company_id;

        abort_unless(
            $companyId,
            403,
            'Company context is not available.'
        );

        /*
        |--------------------------------------------------------------------------
        | Retrieve Report Filters
        |--------------------------------------------------------------------------
        */

        $reportFilters = $request->session()->get(
            'sales_summary_filters_' . $user->id
        );

        if (!$reportFilters) {
            return redirect()->route(
                'reports.sales.summary.parameters'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Company Context
        |--------------------------------------------------------------------------
        */

        abort_unless(
            (int) $reportFilters['company_id'] === (int) $companyId,
            403,
            'Invalid report company context.'
        );

        /*
        |--------------------------------------------------------------------------
        | Accessible Branches
        |--------------------------------------------------------------------------
        */

        $canViewAllBranches = $user->can(
            'reports.view-all-branches'
        );

        if ($canViewAllBranches) {
            $branches = Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'code',
                    'name',
                ]);
        } else {
            $branches = $user->branches()
                ->where('branches.company_id', $companyId)
                ->where('branches.is_active', true)
                ->orderBy('branches.name')
                ->get([
                    'branches.id',
                    'branches.code',
                    'branches.name',
                ]);
        }

        $accessibleBranchIds = $branches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Validate Selected Branches
        |--------------------------------------------------------------------------
        */

        $selectedBranchIds = array_map(
            'intval',
            $reportFilters['branch_ids'] ?? []
        );

        abort_if(
            empty($selectedBranchIds),
            403,
            'No report branches available.'
        );

        foreach ($selectedBranchIds as $branchId) {
            abort_unless(
                in_array(
                    $branchId,
                    $accessibleBranchIds,
                    true
                ),
                403,
                'You no longer have access to one of the selected branches.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Report
        |--------------------------------------------------------------------------
        */

        $report = $this->salesSummaryService->generate(
            $reportFilters
        );

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Reports/Results/SalesSummary',
            [
                'report' => $report,

                'branches' => Branch::query()
                    ->where('company_id', $companyId)
                    ->whereIn('id', $selectedBranchIds)
                    ->orderBy('name')
                    ->get([
                        'id',
                        'code',
                        'name',
                    ]),
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

        abort_unless(
            $companyId,
            403,
            'Company context is not available.'
        );

        /*
        |--------------------------------------------------------------------------
        | Retrieve Report Filters
        |--------------------------------------------------------------------------
        */

        $reportFilters = $request->session()->get(
            'sales_summary_filters_' . $user->id
        );

        abort_unless(
            $reportFilters,
            404,
            'Sales Summary filters not found.'
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Company Context
        |--------------------------------------------------------------------------
        */

        abort_unless(
            isset($reportFilters['company_id']) &&
            (int) $reportFilters['company_id'] === (int) $companyId,
            403,
            'Invalid report company context.'
        );

        /*
        |--------------------------------------------------------------------------
        | Accessible Branches
        |--------------------------------------------------------------------------
        */

        if ($user->can('reports.view-all-branches')) {
            $accessibleBranchIds = Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
        } else {
            $accessibleBranchIds = $user->branches()
                ->where('branches.company_id', $companyId)
                ->where('branches.is_active', true)
                ->pluck('branches.id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Selected Branches
        |--------------------------------------------------------------------------
        */

        $selectedBranchIds = array_map(
            'intval',
            $reportFilters['branch_ids'] ?? []
        );

        abort_if(
            empty($selectedBranchIds),
            403,
            'No report branches available.'
        );

        foreach ($selectedBranchIds as $branchId) {
            abort_unless(
                in_array(
                    $branchId,
                    $accessibleBranchIds,
                    true
                ),
                403,
                'You no longer have access to one of the selected branches.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Load Selected Branches
        |--------------------------------------------------------------------------
        */

        $branches = Branch::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $selectedBranchIds)
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Generate Report
        |--------------------------------------------------------------------------
        */

        $report = $this->salesSummaryService->generate(
            $reportFilters
        );

        return [
            'report' => $report,
            'branches' => $branches,
            'filters' => $reportFilters,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Print Sales Summary
    |--------------------------------------------------------------------------
    */

    public function print(Request $request)
    {
        return view(
            'print.Report.sales-summary',
            $this->getExportData($request)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF Sales Summary
    |--------------------------------------------------------------------------
    */

    public function exportPdf(Request $request)
    {
        $data = $this->getExportData($request);

        return Pdf::loadView(
            'pdf.Report.sales-summary',
            $data
        )
            ->setPaper('a4', 'landscape')
            ->download('sales-summary.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Excel Sales Summary
    |--------------------------------------------------------------------------
    */

    public function exportExcel(Request $request)
    {
        return Excel::download(
            new SalesSummaryExport(
                $this->getExportData($request)
            ),
            'sales-summary.xlsx'
        );
    }
}