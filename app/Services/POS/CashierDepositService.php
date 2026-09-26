<?php

namespace App\Services\POS;

use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Branch;
use App\Models\POS\CashierDeposit;
use App\Models\POS\CashierSession;
use App\Models\User;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Services\Accounting\JournalEntryService;
use Carbon\Carbon;
class CashierDepositService
{
    protected CodeGeneratorService $codeGeneratorService;
        protected JournalEntryService $journalEntryService;
        
    public function __construct(
    CodeGeneratorService $codeGeneratorService,
    JournalEntryService $journalEntryService
    ) {
        $this->codeGeneratorService =
            $codeGeneratorService;

        $this->journalEntryService =
            $journalEntryService;
    }

    public function create(
        User $user,
        int $destinationAccountId,
        float $amount,
        string $depositedAt,
        ?string $note = null
    ): CashierDeposit {
        return DB::transaction(function () use (
            $user,
            $destinationAccountId,
            $amount,
            $depositedAt,
            $note
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
            | Branch Validation
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
            | Active Cashier Session
            |--------------------------------------------------------------------------
            */

            $session =
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

            if (! $session) {
                throw ValidationException::withMessages([
                    'session' =>
                        'Cashier session belum dibuka.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Destination Account
            |--------------------------------------------------------------------------
            */

           $destinationAccount =
                ChartOfAccount::query()
                    ->whereKey($destinationAccountId)
                    ->where(
                        'company_id',
                        $user->company_id
                    )
                    ->where('status', true)
                    ->where('is_posting', true)
                    ->lockForUpdate()
                    ->first();

            if (! $destinationAccount) {
                throw ValidationException::withMessages([
                    'destination_account_id' =>
                        'Destination account tidak valid untuk company ini.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Deposit Amount
            |--------------------------------------------------------------------------
            */

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Deposit amount harus lebih besar dari 0.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Deposit Number
            |--------------------------------------------------------------------------
            */

            $depositNumber =
                $this->codeGeneratorService
                    ->next('cashier_deposit');

            /*
            |--------------------------------------------------------------------------
            | Create Deposit
            |--------------------------------------------------------------------------
            */

            $deposit =
                CashierDeposit::create([
                    'company_id' =>
                        $user->company_id,

                    'branch_id' =>
                        $branch->id,

                    'cashier_session_id' =>
                        $session->id,

                    'deposit_number' =>
                        $depositNumber,

                    'destination_account_id' =>
                        $destinationAccount->id,

                    'amount' =>
                        $amount,

                    'deposited_at' =>
                        $depositedAt,

                    'status' =>
                        'draft',

                    'note' =>
                        $note,

                    'created_by' =>
                        $user->id,

                    'updated_by' =>
                        $user->id,
                ]);

            return $deposit->fresh([
                'company',
                'branch',
                'cashierSession',
                'destinationAccount',
                'createdBy',
                'updatedBy',
            ]);
        });
    }
public function post(
    User $user,
    CashierDeposit $deposit
): CashierDeposit {
    return DB::transaction(function () use (
        $user,
        $deposit
    ) {

        /*
        |--------------------------------------------------------------------------
        | Lock Deposit
        |--------------------------------------------------------------------------
        */

        $deposit = CashierDeposit::query()
            ->lockForUpdate()
            ->findOrFail($deposit->id);

        /*
        |--------------------------------------------------------------------------
        | Validate Status
        |--------------------------------------------------------------------------
        */

        if ($deposit->status !== 'draft') {
            throw ValidationException::withMessages([
                'deposit' =>
                    'Only Draft deposit can be posted.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate User Context
        |--------------------------------------------------------------------------
        */

        if (
            (int) $deposit->company_id !==
            (int) $user->company_id
        ) {
            throw ValidationException::withMessages([
                'deposit' =>
                    'Deposit tidak sesuai dengan company user.',
            ]);
        }

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

        if (
            (int) $deposit->branch_id !==
            (int) $currentBranchId
        ) {
            throw ValidationException::withMessages([
                'branch' =>
                    'Deposit tidak sesuai dengan current branch.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Cashier Session
        |--------------------------------------------------------------------------
        */

        $session = CashierSession::query()
            ->whereKey($deposit->cashier_session_id)
            ->lockForUpdate()
            ->first();

        if (! $session) {
            throw ValidationException::withMessages([
                'session' =>
                    'Cashier session tidak ditemukan.',
            ]);
        }

        if ($session->status !== 'open') {
            throw ValidationException::withMessages([
                'session' =>
                    'Cashier session sudah ditutup.',
            ]);
        }

        if (
            (int) $session->user_id !==
            (int) $user->id
        ) {
            throw ValidationException::withMessages([
                'session' =>
                    'Deposit bukan milik cashier session user ini.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Destination Account
        |--------------------------------------------------------------------------
        */

        $destinationAccount =
        ChartOfAccount::query()
            ->whereKey(
                $deposit->destination_account_id
            )
            ->where(
                'company_id',
                $user->company_id
            )
            ->where('status', true)
            ->where('is_posting', true)
            ->first();

        if (! $destinationAccount) {
            throw ValidationException::withMessages([
                'destination_account_id' =>
                    'Destination account tidak valid.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Cashier Cash Account
        |--------------------------------------------------------------------------
        */

        $cashierCashAccount =
            ChartOfAccount::query()
                ->whereKey(
                    $session->cash_account_id
                )
                ->where(
                    'company_id',
                    $user->company_id
                )
              //  ->where(
              //      'allow_transaction',
              //      true
               // )
                ->first();

        if (! $cashierCashAccount) {
            throw ValidationException::withMessages([
                'cash_account_id' =>
                    'Cashier cash account tidak valid.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Accounting Journal - CASH
        |--------------------------------------------------------------------------
        */

        $accountingJournal =
            AccountingJournal::query()
                ->where(
                    'company_id',
                    $user->company_id
                )
                ->where(
                    'code',
                    'CSH'
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();

        if (! $accountingJournal) {
            throw ValidationException::withMessages([
                'accounting_journal' =>
                    'Cash Journal belum tersedia untuk company ini.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Fiscal Year
        |--------------------------------------------------------------------------
        */

        $depositDate =
            Carbon::parse(
                $deposit->deposited_at
            )->toDateString();

        $fiscalYear =
            FiscalYear::query()
                ->where(
                    'company_id',
                    $user->company_id
                )
                ->where(
                    'status',
                    'Open'
                )
                ->whereDate(
                    'start_date',
                    '<=',
                    $depositDate
                )
                ->whereDate(
                    'end_date',
                    '>=',
                    $depositDate
                )
                ->first();

        if (! $fiscalYear) {
            throw ValidationException::withMessages([
                'deposited_at' =>
                    'Tidak ditemukan fiscal year yang terbuka untuk tanggal deposit.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve Accounting Period
        |--------------------------------------------------------------------------
        */

        $accountingPeriod =
            AccountingPeriod::query()
                ->where(
                    'company_id',
                    $user->company_id
                )
                ->where(
                    'fiscal_year_id',
                    $fiscalYear->id
                )
                ->where(
                    'status',
                    'Open'
                )
                ->whereDate(
                    'start_date',
                    '<=',
                    $depositDate
                )
                ->whereDate(
                    'end_date',
                    '>=',
                    $depositDate
                )
                ->first();

        if (! $accountingPeriod) {
            throw ValidationException::withMessages([
                'deposited_at' =>
                    'Tidak ditemukan accounting period yang terbuka untuk tanggal deposit.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Journal - DRAFT
        |--------------------------------------------------------------------------
        */

        $journalEntry =
            $this->journalEntryService->create([
                'branch_id' =>
                    $deposit->branch_id,

                'accounting_journal_id' =>
                    $accountingJournal->id,

                'fiscal_year_id' =>
                    $fiscalYear->id,

                'accounting_period_id' =>
                    $accountingPeriod->id,

                'entry_date' =>
                    $deposit->deposited_at,

                'reference' =>
                    $deposit->deposit_number,

                'description' =>
                    'Cash deposit ' .
                    $deposit->deposit_number,

                'lines' => [
                    [
                        'account_id' =>
                            $destinationAccount->id,

                        'description' =>
                            'Cash deposit to ' .
                            $destinationAccount->name,

                        'debit' =>
                            $deposit->amount,

                        'credit' =>
                            0,
                    ],

                    [
                        'account_id' =>
                            $cashierCashAccount->id,

                        'description' =>
                            'Cashier cash deposit ' .
                            $deposit->deposit_number,

                        'debit' =>
                            0,

                        'credit' =>
                            $deposit->amount,
                    ],
                ],
            ]);

        /*
        |--------------------------------------------------------------------------
        | Post Journal
        |--------------------------------------------------------------------------
        */

        $this->journalEntryService->post(
            $journalEntry
        );

        /*
        |--------------------------------------------------------------------------
        | Mark Deposit Posted
        |--------------------------------------------------------------------------
        */

        $deposit->update([
            'status' =>
                'posted',

            'updated_by' =>
                $user->id,
        ]);

        return $deposit->fresh([
            'company',
            'branch',
            'cashierSession',
            'destinationAccount',
            'createdBy',
            'updatedBy',
        ]);
    });
}
    public function getActiveSession(
        User $user
    ): ?CashierSession {
        $currentBranchId =
            session('current_branch_id');

        if (! $currentBranchId) {
            return null;
        }

        return CashierSession::query()
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
}