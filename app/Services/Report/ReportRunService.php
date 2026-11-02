<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Branch;
use App\Models\Report\Report;
use App\Models\Report\ReportRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Permission
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $user->can('reports.view'),
            403,
            'You are not authorized to view reports.'
        );

        $canViewAllBranches = $user->can(
            'reports.view-all-branches'
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Company Context
        |--------------------------------------------------------------------------
        */

        $companyId = $user->company_id;

        abort_unless(
            $companyId,
            403,
            'Company context is not available.'
        );

        $currentBranchId = session('current_branch_id');

        /*
        |--------------------------------------------------------------------------
        | Accessible Branches
        |--------------------------------------------------------------------------
        */

        if ($canViewAllBranches) {

            // All active branches belonging to this company.

            $accessibleBranches = Branch::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'code',
                    'name',
                ]);

        } else {

            // Only branches assigned to this user.

            $accessibleBranches = $user->branches()
                ->where('branches.company_id', $companyId)
                ->where('branches.is_active', true)
                ->orderBy('branches.name')
                ->get([
                    'branches.id',
                    'branches.code',
                    'branches.name',
                ]);

        }

        $accessibleBranchIds = $accessibleBranches
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        abort_if(
            empty($accessibleBranchIds),
            403,
            'No accessible branches found.'
        );

        /*
        |--------------------------------------------------------------------------
        | Selected Branch
        |--------------------------------------------------------------------------
        */

        if ($canViewAllBranches) {

            $selectedBranchId = $validated['branch_id'] ?? null;

            if ($selectedBranchId !== null) {

                abort_unless(
                    in_array(
                        (int) $selectedBranchId,
                        $accessibleBranchIds,
                        true
                    ),
                    403,
                    'You do not have access to this branch.'
                );

                $selectedBranchId = (int) $selectedBranchId;
            }

        } else {

            $selectedBranchId = $validated['branch_id']
                ?? $currentBranchId;

            abort_unless(
                $selectedBranchId &&
                in_array(
                    (int) $selectedBranchId,
                    $accessibleBranchIds,
                    true
                ),
                403,
                'You do not have access to this branch.'
            );

            $selectedBranchId = (int) $selectedBranchId;
        }

        /*
        |--------------------------------------------------------------------------
        | Report Catalog
        |--------------------------------------------------------------------------
        */

        $reports = Report::query()
            ->active()
            ->when(
                !empty($validated['search']),
                function ($query) use ($validated) {

                    $search = $validated['search'];

                    $query->where(function ($q) use ($search) {

                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");

                    });
                }
            )
            ->when(
                !empty($validated['category']),
                fn ($query) => $query->where(
                    'category',
                    $validated['category']
                )
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories = Report::query()
            ->active()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        /*
        |--------------------------------------------------------------------------
        | Recently Run Reports
        |--------------------------------------------------------------------------
        */

        $recentRuns = ReportRun::query()
            ->with([
                'report:id,code,name,category',
                'user:id,name',
            ])
            ->where('company_id', $companyId)
            ->when(
                $selectedBranchId !== null,
                fn ($query) => $query->where(
                    'branch_id',
                    $selectedBranchId
                )
            )
            ->when(
                !$canViewAllBranches,
                fn ($query) => $query->whereIn(
                    'branch_id',
                    $accessibleBranchIds
                )
            )
            ->latest('started_at')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Current Branch
        |--------------------------------------------------------------------------
        */

        $currentBranch = Branch::query()
            ->where('company_id', $companyId)
            ->find($currentBranchId);

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Report/ReportDasboards',
            [
                'reports' => $reports,

                'categories' => $categories,

                'recentRuns' => $recentRuns,

                'branches' => $canViewAllBranches
                    ? $accessibleBranches
                    : [],

                'filters' => [
                    'search' => $validated['search'] ?? '',
                    'category' => $validated['category'] ?? '',
                    'branch_id' => $selectedBranchId,
                ],

                'permissions' => [
                    'view' => true,
                    'viewAllBranches' => $canViewAllBranches,
                ],

                'currentBranch' => $currentBranch ? [
                    'id' => $currentBranch->id,
                    'name' => $currentBranch->name,
                ] : null,
            ]
        );
    }
}