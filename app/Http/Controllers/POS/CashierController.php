<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BranchAccountMapping;
use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Warehouse;
use App\Services\POS\CashierSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\POS\CashierDepositService;
class CashierController extends Controller
{
    protected CashierSessionService $cashierSessionService;
    protected CashierDepositService $cashierDepositService;
    public function __construct(
    CashierSessionService $cashierSessionService,
    CashierDepositService $cashierDepositService
    ) {
        $this->cashierSessionService =
            $cashierSessionService;

        $this->cashierDepositService =
            $cashierDepositService;
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
        |--------------------------------------------------------------------
        | Cash Deposit Date Filter
        |--------------------------------------------------------------------
        */

        $depositDateFrom =
            $request->input(
                'deposit_date_from'
            );

        $depositDateTo =
            $request->input(
                'deposit_date_to'
            );

        if (
            $depositDateFrom &&
            $depositDateTo &&
            $depositDateFrom > $depositDateTo
        ) {
            [
                $depositDateFrom,
                $depositDateTo
            ] = [
                $depositDateTo,
                $depositDateFrom,
            ];
        }
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

        $cashierDeposits =
            $this->cashierDepositService
                ->getDashboardDeposits(
                    $user,
                    $depositDateFrom,
                    $depositDateTo
                );

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
        | Cash Deposit Destination Accounts
        |--------------------------------------------------------------------------
        */

        $depositAccounts = ChartOfAccount::query()
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'status',
                true
            )
            ->where(
                'is_header',
                false
            )
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
            ]);

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

                'depositAccounts' =>
                    $depositAccounts,

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

                'cashierDeposits' =>
                    $cashierDeposits,
            ]
        );
    }
}
