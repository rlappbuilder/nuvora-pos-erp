<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Branch;
use App\Services\Report\SalesByProductService;
use App\Exports\Report\SalesByProductExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class SalesByProductController extends Controller
{
    protected SalesByProductService $salesByProductService;

    public function __construct(
        SalesByProductService $salesByProductService
    ) {
        $this->salesByProductService = $salesByProductService;
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
    | Resolve Report Products
    |--------------------------------------------------------------------------
    */

    private function resolveReportProducts(
        array $selectedProductIds
    ): array {

        $selectedIds = collect($selectedProductIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($selectedIds)) {

            return [
                'selected_ids' => [],
                'products' => collect(),
                'product_names' => [],
                'product_label' => 'All Products',
            ];
        }

        $products = DB::table('products')
            ->whereIn('id', $selectedIds)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'sku',
                'name',
            ]);

        abort_unless(
            $products->count() === count($selectedIds),
            422,
            'One or more selected products are invalid or inactive.'
        );

        return [
            'selected_ids' => $selectedIds,

            'products' => $products,

            'product_names' => $products
                ->pluck('name')
                ->values()
                ->all(),

            'product_label' => $products
                ->pluck('name')
                ->implode(', '),
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
        | Products
        |--------------------------------------------------------------------------
        */

        $products = DB::table('products')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'sku',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Render Parameters
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Reports/Parameters/SalesByProduct',
            [
                'branches' => $branches,

                'products' => $products,

                'canViewAllBranches' => $canViewAllBranches,

                'filters' => [

                    'date_from' => now()
                        ->startOfMonth()
                        ->toDateString(),

                    'date_to' => now()
                        ->toDateString(),

                    'branch_ids' => $defaultBranchIds,

                    'source' => 'all',

                    'product_ids' => [],
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

            'product_ids' => [
                'present',
                'array',
            ],

            'product_ids.*' => [
                'required',
                'integer',
                'distinct',
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
        | Resolve Product Selection
        |--------------------------------------------------------------------------
        */

        $productScope = $this->resolveReportProducts(
            $validated['product_ids']
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

            'product_ids' => $productScope['selected_ids'],

            'product_names' => $productScope['product_names'],

            'product_label' => $productScope['product_label'],

        ];

        /*
        |--------------------------------------------------------------------------
        | Store Filters
        |--------------------------------------------------------------------------
        */

        $request->session()->put(
            'sales_by_product_filters_' . $user->id,
            $filters
        );

        return redirect()->route(
            'reports.sales.by-product.results'
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
            'sales_by_product_filters_' . $user->id
        );

        abort_unless(
            $filters,
            404,
            'Sales by Product filters not found.'
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
        | Revalidate Product Selection
        |--------------------------------------------------------------------------
        */

        $productScope = $this->resolveReportProducts(
            $filters['product_ids'] ?? []
        );

        $filters['product_ids'] =
            $productScope['selected_ids'];

        $filters['product_names'] =
            $productScope['product_names'];

        $filters['product_label'] =
            $productScope['product_label'];

        /*
        |--------------------------------------------------------------------------
        | Generate Report
        |--------------------------------------------------------------------------
        */

        $report = $this->salesByProductService->generate(
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
            'Reports/Results/SalesByProduct',
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
            'sales_by_product_filters_' . $user->id
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
        | Revalidate Product Selection
        |--------------------------------------------------------------------------
        */

        $productScope = $this->resolveReportProducts(
            $reportFilters['product_ids'] ?? []
        );

        $reportFilters['product_ids'] =
            $productScope['selected_ids'];

        $reportFilters['product_names'] =
            $productScope['product_names'];

        $reportFilters['product_label'] =
            $productScope['product_label'];

        /*
        |--------------------------------------------------------------------------
        | Generate Complete Report
        |--------------------------------------------------------------------------
        */

        $report = $this->salesByProductService->generate(
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
    | Print Sales by Product
    |--------------------------------------------------------------------------
    */

    public function print(Request $request)
    {
        $data = $this->getExportData($request);

        return view(
            'print.Report.sales-by-product',
            $data
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PDF Sales by Product
    |--------------------------------------------------------------------------
    */

    public function exportPdf(Request $request)
    {
        $data = $this->getExportData($request);

        return Pdf::loadView(
            'pdf.Report.sales-by-product',
            $data
        )
            ->setPaper('a4', 'landscape')
            ->download('sales-by-product.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Excel Sales by Product
    |--------------------------------------------------------------------------
    */

    public function exportExcel(Request $request)
    {
        $data = $this->getExportData($request);

        return Excel::download(
            new SalesByProductExport($data),
            'sales-by-product.xlsx'
        );
    }
}