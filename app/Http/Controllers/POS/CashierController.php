<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Warehouse;
use App\Services\POS\CashierSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        $currentBranchId =
            session('current_branch_id');

        $activeSession =
            $this->cashierSessionService
                ->getActiveSession($user);

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
        | Cash Accounts
        |--------------------------------------------------------------------------
        */

        $cashAccounts = ChartOfAccount::query()
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'status',
                true
            )
            ->where(
                'is_posting',
                true
            )
            ->whereHas(
                'accountCategory',
                function ($query) {
                    $query
                        ->where(
                            'code',
                            '110100'
                        )
                        ->where(
                            'status',
                            true
                        );
                }
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

                'warehouses' =>
                    $warehouses,
            ]
        );
    }
}