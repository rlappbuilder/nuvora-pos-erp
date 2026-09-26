<?php

namespace App\Services\POS;

use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\POS\CashierSession;
use App\Models\User;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashierSessionService
{
    protected CodeGeneratorService $codeGeneratorService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService
    ) {
        $this->codeGeneratorService =
            $codeGeneratorService;
    }

    /*
    |--------------------------------------------------------------------------
    | Open Session
    |--------------------------------------------------------------------------
    */

    public function open(
        User $user,
        int $warehouseId,
        int $cashAccountId,
        float $openingBalance
    ): CashierSession {
        return DB::transaction(function () use (
            $user,
            $warehouseId,
            $cashAccountId,
            $openingBalance
        ) {

            /*
            |--------------------------------------------------------------------------
            | Current Branch
            |--------------------------------------------------------------------------
            */

            $currentBranchId =
                session('current_branch_id');

            if (! $currentBranchId) {
                throw ValidationException::withMessages([
                    'branch' =>
                        'Current branch belum dipilih.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Lock Branch
            |--------------------------------------------------------------------------
            */

            $branch = Branch::query()
                ->whereKey($currentBranchId)
                ->lockForUpdate()
                ->first();

            if (! $branch) {
                throw ValidationException::withMessages([
                    'branch' =>
                        'Current branch tidak ditemukan.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Company Validation
            |--------------------------------------------------------------------------
            */

            if (
                (int) $branch->company_id !==
                (int) $user->company_id
            ) {
                throw ValidationException::withMessages([
                    'branch' =>
                        'Current branch tidak sesuai dengan company user.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | User Branch Access
            |--------------------------------------------------------------------------
            */

            $hasBranchAccess =
                $user->branches()
                    ->where(
                        'branches.id',
                        $branch->id
                    )
                    ->exists();

            if (! $hasBranchAccess) {
                throw ValidationException::withMessages([
                    'branch' =>
                        'User tidak memiliki akses ke current branch.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Warehouse
            |--------------------------------------------------------------------------
            */

            $warehouse = Warehouse::query()
                ->whereKey($warehouseId)
                ->where(
                    'branch_id',
                    $branch->id
                )
                ->where(
                    'status',
                    true
                )
                ->lockForUpdate()
                ->first();

            if (! $warehouse) {
                throw ValidationException::withMessages([
                    'warehouse_id' =>
                        'Warehouse tidak valid untuk current branch.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Existing Open Session
            |--------------------------------------------------------------------------
            */

            $existingSession =
                CashierSession::query()
                    ->where(
                        'company_id',
                        $user->company_id
                    )
                    ->where(
                        'branch_id',
                        $branch->id
                    )
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'status',
                        'open'
                    )
                    ->lockForUpdate()
                    ->first();

            if ($existingSession) {
                throw ValidationException::withMessages([
                    'session' =>
                        'Cashier masih memiliki session yang sedang terbuka.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Cash Account
            |--------------------------------------------------------------------------
            |
            | Cashier hanya boleh menggunakan posting account
            | dari kategori Cash & Bank (110100).
            |
            */

            $cashAccount =
                ChartOfAccount::query()
                    ->whereKey($cashAccountId)
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
                    ->lockForUpdate()
                    ->first();

            if (! $cashAccount) {
                throw ValidationException::withMessages([
                    'cash_account_id' =>
                        'Cash account tidak valid untuk company ini.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Previous Session
            |--------------------------------------------------------------------------
            */

            $previousSession =
                CashierSession::query()
                    ->where(
                        'company_id',
                        $user->company_id
                    )
                    ->where(
                        'branch_id',
                        $branch->id
                    )
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->where(
                        'status',
                        'closed'
                    )
                    ->latest('closed_at')
                    ->lockForUpdate()
                    ->first();

            /*
            |--------------------------------------------------------------------------
            | Opening Balance
            |--------------------------------------------------------------------------
            |
            | If a previous session has carry-forward cash,
            | it becomes the opening balance automatically.
            |
            */

            if ($previousSession) {

                $carryForward =
                    $this->calculateCarryForward(
                        $previousSession
                    );

                $openingBalance =
                    $carryForward;
            }

            /*
            |--------------------------------------------------------------------------
            | Session Number
            |--------------------------------------------------------------------------
            */

            $sessionNumber =
                $this->codeGeneratorService
                    ->next('cashier_session');

            /*
            |--------------------------------------------------------------------------
            | Create Session
            |--------------------------------------------------------------------------
            */

            $session =
                CashierSession::create([
                    'company_id' =>
                        $user->company_id,

                    'branch_id' =>
                        $branch->id,

                    'warehouse_id' =>
                        $warehouse->id,

                    'user_id' =>
                        $user->id,

                    'cash_account_id' =>
                        $cashAccount->id,

                    'previous_session_id' =>
                        $previousSession?->id,

                    'session_number' =>
                        $sessionNumber,

                    'opened_at' =>
                        now(),

                    'opening_balance' =>
                        $openingBalance,

                    'status' =>
                        'open',
                ]);

            return $session->fresh([
                'company',
                'branch',
                'warehouse',
                'user',
                'cashAccount',
                'previousSession',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Carry Forward
    |--------------------------------------------------------------------------
    */

    protected function calculateCarryForward(
        CashierSession $session
    ): float {
        $closingBalance =
            (float) (
                $session->closing_balance ?? 0
            );

        $totalPostedDeposits =
            $session->deposits()
                ->where(
                    'status',
                    'posted'
                )
                ->sum('amount');

        return max(
            0,
            $closingBalance -
            (float) $totalPostedDeposits
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Get Active Session
    |--------------------------------------------------------------------------
    */

    public function getActiveSession(
        User $user
    ): ?CashierSession {

        $currentBranchId =
            session('current_branch_id');

        if (! $currentBranchId) {
            return null;
        }

        return CashierSession::query()
            ->with([
                'company',
                'branch',
                'warehouse',
                'user',
                'cashAccount',
                'previousSession',
            ])
            ->where(
                'company_id',
                $user->company_id
            )
            ->where(
                'branch_id',
                $currentBranchId
            )
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'status',
                'open'
            )
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Close Session
    |--------------------------------------------------------------------------
    */

    public function close(
        CashierSession $session,
        float $closingBalance,
        ?string $closingNote = null
    ): CashierSession {

        return DB::transaction(function () use (
            $session,
            $closingBalance,
            $closingNote
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Session
            |--------------------------------------------------------------------------
            */

            $session = CashierSession::query()
                ->whereKey($session->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $session->status !== 'open'
            ) {
                throw ValidationException::withMessages([
                    'session' =>
                        'Cashier session sudah ditutup.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Close
            |--------------------------------------------------------------------------
            */

            $session->update([
                'closed_at' =>
                    now(),

                'closing_balance' =>
                    $closingBalance,

                'status' =>
                    'closed',

                'closing_note' =>
                    $closingNote,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Return Fresh Session
            |--------------------------------------------------------------------------
            */

            return $session->fresh([
                'company',
                'branch',
                'warehouse',
                'user',
                'cashAccount',
                'previousSession',
            ]);
        });
    }
}