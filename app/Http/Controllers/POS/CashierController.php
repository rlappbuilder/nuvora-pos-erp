<?php

namespace App\Http\Controllers\POS;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
//use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Warehouse;
use App\Services\POS\CashierSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Accounting\BranchAccountMapping;
class CashierController extends Controller
{
    protected CashierSessionService $cashierSessionService;

    public function __construct(
        CashierSessionService $cashierSessionService
    ) {
        $this->cashierSessionService =
            $cashierSessionService;
    }

    public function index(
    Request $request
) {
    $user = $request->user();

    /*
    |--------------------------------------------------------------------------
    | Current Branch
    |--------------------------------------------------------------------------
    */

    $currentBranchId =
        session('current_branch_id');

    /*
    |--------------------------------------------------------------------------
    | Session Report
    |--------------------------------------------------------------------------
    */

    $dateFrom = $request->input(
        'date_from',
        now()->startOfMonth()->toDateString()
    );

    $dateTo = $request->input(
        'date_to',
        now()->toDateString()
    );

    if ($dateFrom > $dateTo) {
        [$dateFrom, $dateTo] = [
            $dateTo,
            $dateFrom,
        ];
    }

    $sessionReport =
        $this->cashierSessionService
            ->getSessionReport(
                $user,
                $dateFrom,
                $dateTo
            );

    /*
    |--------------------------------------------------------------------------
    | Active Session
    |--------------------------------------------------------------------------
    */

    $activeSession =
        $this->cashierSessionService
            ->getActiveSession($user);

    /*
    |--------------------------------------------------------------------------
    | Dashboard Summary
    |--------------------------------------------------------------------------
    */

    $dashboardSummary =
        $this->cashierSessionService
            ->getDashboardSummary($user);

    $dashboardAnalytics =
      $this->cashierSessionService
        ->getDashboardAnalytics($user);

    /*
    |--------------------------------------------------------------------------
    | Close Summary
    |--------------------------------------------------------------------------
    */

    $closeSummary = $activeSession
        ? $this->cashierSessionService
            ->getCloseSummary($activeSession)
        : null;

    /*
    |--------------------------------------------------------------------------
    | Opening Context
    |--------------------------------------------------------------------------
    */

    $openingContext =
        $this->cashierSessionService
            ->getOpeningContext($user);

    /*
    |--------------------------------------------------------------------------
    | Warehouses
    |--------------------------------------------------------------------------
    */

    $warehouses = Warehouse::query()
        ->where(
            'branch_id',
            $currentBranchId
        )
        ->where(
            'status',
            true
        )
        ->orderBy('code')
        ->get([
            'id',
            'code',
            'name',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Cash Account
    |--------------------------------------------------------------------------
    */

    $cashAccountMapping =
        BranchAccountMapping::query()
            ->with('account')
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'branch_id',
                $currentBranchId
            )
            ->where(
                'key',
                'cashier_drawer'
            )
            ->where(
                'is_active',
                true
            )
            ->first();

    $cashAccounts = collect();

    if (
        $cashAccountMapping &&
        $cashAccountMapping->account
    ) {
        $cashAccounts = collect([
            [
                'id' =>
                    $cashAccountMapping
                        ->account
                        ->id,

                'code' =>
                    $cashAccountMapping
                        ->account
                        ->code,

                'name' =>
                    $cashAccountMapping
                        ->account
                        ->name,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    return Inertia::render(
        'POS/Cashier/Index',
        [
            'activeSession' =>
                $activeSession,

            'cashAccounts' =>
                $cashAccounts,

            'warehouses' =>
                $warehouses,

            'openingContext' =>
                $openingContext,

            'closeSummary' =>
                $closeSummary,

            'sessionReport' =>
                $sessionReport,

            'dashboardSummary' =>
                $dashboardSummary,

            'dashboardAnalytics' =>
                 $dashboardAnalytics,
        ]
    );
}
}