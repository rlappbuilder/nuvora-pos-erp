<?php

namespace App\Services\POS;

use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalYear;
use App\Models\MasterData\Branch;
use App\Models\POS\CashierDeposit;
use App\Models\POS\CashierSession;
use App\Models\User;
use App\Services\Accounting\JournalEntryService;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashierDepositService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected DocumentActivityService $documentActivityService;

    protected JournalEntryService $journalEntryService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService,
        DocumentActivityService $documentActivityService,
        JournalEntryService $journalEntryService
    ) {
        $this->codeGeneratorService =
            $codeGeneratorService;

        $this->documentActivityService =
            $documentActivityService;

        $this->journalEntryService =
            $journalEntryService;
    }

    /*
    |-----------------------------------------------------------------------
    | Create
    |-----------------------------------------------------------------------
    */

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
                    ->where(
                        'status',
                        true
                    )
                    ->where(
                        'is_posting',
                        true
                    )
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
            | Available Cash Validation
            |--------------------------------------------------------------------------
            */

            $availableCash =
                $this->getAvailableCash($session);

            if ($amount > $availableCash) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Deposit amount melebihi saldo cash drawer yang tersedia.',
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
        |-------------------------------------------------------------------
        | Create Deposit
        |-------------------------------------------------------------------
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
                    'Submitted',

                'note' =>
                    $note,

                'created_by' =>
                    $user->id,

                'updated_by' =>
                    $user->id,

                'submitted_by' =>
                    $user->id,

                'submitted_at' =>
                    now(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Activity - SUBMITTED
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService->record(
            $deposit,
            'SUBMITTED',
            null,
            'Submitted',
            'Cash deposit submitted for supervisor approval.'
        );

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

      /*
    |-----------------------------------------------------------------------
    | Submit / Resubmit
    |-----------------------------------------------------------------------
    */

    public function submit(
        User $user,
        CashierDeposit $deposit
    ): CashierDeposit {
        return DB::transaction(function () use (
            $user,
            $deposit
        ) {

            $deposit = $this->lockDeposit(
                $deposit
            );

            $this->validateUserContext(
                $user,
                $deposit
            );

            if ($deposit->status !== 'Rejected') {
                throw ValidationException::withMessages([
                    'deposit' =>
                        'Only Rejected deposit can be resubmitted.',
                ]);
            }

            $oldStatus = $deposit->status;

            $deposit->update([
                'status' =>
                    'Submitted',

                'submitted_at' =>
                    now(),

                'submitted_by' =>
                    $user->id,

                'updated_by' =>
                    $user->id,
            ]);

            $this->documentActivityService->record(
                $deposit,
                'RESUBMITTED',
                $oldStatus,
                'Submitted',
                'Cash deposit resubmitted for approval.'
            );

            return $deposit->fresh();
        });
    }
    /*
    |-----------------------------------------------------------------------
    | Approve
    |-----------------------------------------------------------------------
    */

    public function approve(
        User $user,
        CashierDeposit $deposit
    ): CashierDeposit {
        return DB::transaction(function () use (
            $user,
            $deposit
        ) {

            $deposit = $this->lockDeposit(
                $deposit
            );

            $this->validateUserContext(
                $user,
                $deposit
            );

            if ($deposit->status !== 'Submitted') {
                throw ValidationException::withMessages([
                    'deposit' =>
                        'Only Submitted deposit can be approved.',
                ]);
            }

            $deposit->update([
                'status' =>
                    'Approved',

                'approved_at' =>
                    now(),

                'approved_by' =>
                    $user->id,

                'updated_by' =>
                    $user->id,
            ]);

            $this->documentActivityService->record(
                $deposit,
                'APPROVED',
                'Submitted',
                'Approved',
                'Cash deposit approved.'
            );

            return $deposit->fresh();
        });
    }

    /*
    |-----------------------------------------------------------------------
    | Reject
    |-----------------------------------------------------------------------
    */

    public function reject(
        User $user,
        CashierDeposit $deposit,
        string $reason
    ): CashierDeposit {
        return DB::transaction(function () use (
            $user,
            $deposit,
            $reason
        ) {

            $deposit = $this->lockDeposit(
                $deposit
            );

            $this->validateUserContext(
                $user,
                $deposit
            );

            if ($deposit->status !== 'Submitted') {
                throw ValidationException::withMessages([
                    'deposit' =>
                        'Only Submitted deposit can be rejected.',
                ]);
            }

            $reason =
                trim($reason);

            if ($reason === '') {
                throw ValidationException::withMessages([
                    'reason' =>
                        'Rejection reason is required.',
                ]);
            }

            $deposit->update([
                'status' =>
                    'Rejected',

                'rejected_at' =>
                    now(),

                'rejected_by' =>
                    $user->id,

                'rejected_reason' =>
                    $reason,

                'updated_by' =>
                    $user->id,
            ]);

            $this->documentActivityService->record(
                $deposit,
                'REJECTED',
                'Submitted',
                'Rejected',
                $reason
            );

            return $deposit->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Receive
    |--------------------------------------------------------------------------
    */

    public function receive(
        User $user,
        CashierDeposit $deposit
    ): CashierDeposit {
        return DB::transaction(function () use (
            $user,
            $deposit
        ) {

            $deposit = $this->lockDeposit(
                $deposit
            );

            $this->validateUserContext(
                $user,
                $deposit
            );

            if ($deposit->status !== 'Approved') {
                throw ValidationException::withMessages([
                    'deposit' =>
                        'Only Approved deposit can be received.',
                ]);
            }

            $deposit->update([
                'status' =>
                    'Received',

                'received_at' =>
                    now(),

                'received_by' =>
                    $user->id,

                'updated_by' =>
                    $user->id,
            ]);

            $this->documentActivityService->record(
                $deposit,
                'RECEIVED',
                'Approved',
                'Received',
                'Cash deposit physically received.'
            );

            return $deposit->fresh();
        });
    }

    /*
    |-----------------------------------------------------------------------
    | Post
    |-----------------------------------------------------------------------
    */

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

            $deposit =
                $this->lockDeposit(
                    $deposit
                );

            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if ($deposit->status !== 'Received') {
                throw ValidationException::withMessages([
                    'deposit' =>
                        'Only Received deposit can be posted.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Validate User Context
            |--------------------------------------------------------------------------
            */

            $this->validateUserContext(
                $user,
                $deposit
            );

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

            $session =
                CashierSession::query()
                    ->whereKey(
                        $deposit->cashier_session_id
                    )
                    ->lockForUpdate()
                    ->first();

            if (! $session) {
                throw ValidationException::withMessages([
                    'session' =>
                        'Cashier session tidak ditemukan.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Available Cash Validation
            |--------------------------------------------------------------------------
            */

            $availableCash =
                $this->getAvailableCash(
                    $session
                );

            if (
                (float) $deposit->amount >
                $availableCash
            ) {
                throw ValidationException::withMessages([
                    'amount' =>
                        'Deposit amount melebihi saldo cash drawer yang tersedia.',
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
                    ->where(
                        'status',
                        true
                    )
                    ->where(
                        'is_posting',
                        true
                    )
                    ->lockForUpdate()
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
                    ->lockForUpdate()
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
                    'Posted',

                'posted_at' =>
                    now(),

                'posted_by' =>
                    $user->id,

                'updated_by' =>
                    $user->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Activity - POSTED
            |--------------------------------------------------------------------------
            */

            $this->documentActivityService->record(
                $deposit,
                'POSTED',
                'Received',
                'Posted',
                'Cash deposit posted and journal created.'
            );

            return $deposit->fresh([
                'company',
                'branch',
                'cashierSession',
                'destinationAccount',
                'createdBy',
                'updatedBy',
                'submittedBy',
                'approvedBy',
                'rejectedBy',
                'receivedBy',
                'postedBy',
            ]);
        });
    }

    /*
    |-----------------------------------------------------------------------
    | Cancel
    |-----------------------------------------------------------------------
    */

    public function cancel(
        User $user,
        CashierDeposit $deposit,
        string $reason
    ): CashierDeposit {
        return DB::transaction(function () use (
            $user,
            $deposit,
            $reason
        ) {

            $deposit =
                $this->lockDeposit(
                    $deposit
                );

            $this->validateUserContext(
                $user,
                $deposit
            );

            if (! in_array(
                $deposit->status,
                [
                    'Draft',
                    'Submitted',
                    'Approved',
                    'Rejected',
                ],
                true
            )) {
                throw ValidationException::withMessages([
                    'deposit' =>
                        'Deposit tidak dapat dibatalkan pada status saat ini.',
                ]);
            }

            $reason =
                trim($reason);

            if ($reason === '') {
                throw ValidationException::withMessages([
                    'reason' =>
                        'Cancellation reason is required.',
                ]);
            }

            $oldStatus =
                $deposit->status;

            $deposit->update([
                'status' =>
                    'Cancelled',

                'cancelled_at' =>
                    now(),

                'cancelled_by' =>
                    $user->id,

                'cancelled_reason' =>
                    $reason,

                'updated_by' =>
                    $user->id,
            ]);

            $this->documentActivityService->record(
                $deposit,
                'CANCELLED',
                $oldStatus,
                'Cancelled',
                $reason
            );

            return $deposit->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Active Session
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
| Dashboard Deposits
|--------------------------------------------------------------------------
*/

public function getDashboardDeposits(
    User $user,
    ?string $dateFrom = null,
    ?string $dateTo = null
): array {
    $currentBranchId =
        session('current_branch_id');

    if (! $currentBranchId) {
        return [];
    }

    $query = CashierDeposit::query()
        ->with([
            'cashierSession:id,session_number',
            'destinationAccount:id,code,name',
            'createdBy:id,name',
            'submittedBy:id,name',
            'approvedBy:id,name',
            'rejectedBy:id,name',
            'receivedBy:id,name',
            'postedBy:id,name',
            'activities',
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
            'created_by',
            $user->id
        );

    if ($dateFrom) {
        $query->whereDate(
            'created_at',
            '>=',
            $dateFrom
        );
    }

    if ($dateTo) {
        $query->whereDate(
            'created_at',
            '<=',
            $dateTo
        );
    }

    return $query
        ->orderByDesc('created_at')
        ->limit(50)
        ->get()
        ->map(function ($deposit) {
            return [
                'id' =>
                    $deposit->id,

                'deposit_number' =>
                    $deposit->deposit_number,

                'session_number' =>
                    $deposit->cashierSession?->session_number,

                'amount' =>
                    (float) $deposit->amount,

                'deposited_at' =>
                    $deposit->deposited_at,

                'status' =>
                    $deposit->status,

                'note' =>
                    $deposit->note,

                'rejected_reason' =>
                    $deposit->rejected_reason,

                'cancelled_reason' =>
                    $deposit->cancelled_reason,

                'destination_account' =>
                    $deposit->destinationAccount
                        ? [
                            'id' =>
                                $deposit->destinationAccount->id,

                            'code' =>
                                $deposit->destinationAccount->code,

                            'name' =>
                                $deposit->destinationAccount->name,
                        ]
                        : null,

                'created_by' =>
                    $deposit->createdBy?->name,

                'submitted_by' =>
                    $deposit->submittedBy?->name,

                'approved_by' =>
                    $deposit->approvedBy?->name,

                'rejected_by' =>
                    $deposit->rejectedBy?->name,

                'received_by' =>
                    $deposit->receivedBy?->name,

                'posted_by' =>
                    $deposit->postedBy?->name,

                'created_at' =>
                    $deposit->created_at,

                'submitted_at' =>
                    $deposit->submitted_at,

                'approved_at' =>
                    $deposit->approved_at,

                'rejected_at' =>
                    $deposit->rejected_at,

                'received_at' =>
                    $deposit->received_at,

                'posted_at' =>
                    $deposit->posted_at,

                'cancelled_at' =>
                    $deposit->cancelled_at,

                /*
                |--------------------------------------------------------------------------
                | Document Activities
                |--------------------------------------------------------------------------
                */

                'activities' =>
                    $deposit->activities,
            ];
        })
        ->values()
        ->all();
}
    /*
    |-----------------------------------------------------------------------
    | Available Cash
    |-----------------------------------------------------------------------
    */

    public function getAvailableCash(
        CashierSession $session
    ): float {
        $session->loadMissing([
            'deposits',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Opening Cash
        |--------------------------------------------------------------------------
        */

        $openingCash =
            (float) (
                $session->opening_balance ?? 0
            );

        /*
        |--------------------------------------------------------------------------
        | Cash Sales
        |--------------------------------------------------------------------------
        |
        | Only today's cash sales affect the
        | current physical cashier drawer.
        |
        | Backdate sales are intentionally excluded.
        |
        */

        $cashSales =
            (float) DB::table('pos_sale_payments')
                ->join(
                    'pos_sales',
                    'pos_sales.id',
                    '=',
                    'pos_sale_payments.pos_sale_id'
                )
                ->where(
                    'pos_sales.cashier_session_id',
                    $session->id
                )
                ->where(
                    'pos_sales.status',
                    'posted'
                )
                ->where(
                    'pos_sale_payments.payment_method',
                    'cash'
                )
                ->whereDate(
                    'pos_sales.sale_date',
                    now()->toDateString()
                )
                ->sum(
                    'pos_sale_payments.amount'
                );

        /*
        |--------------------------------------------------------------------------
        | Posted Cash Deposit
        |--------------------------------------------------------------------------
        */

        $cashDeposit =
            (float) $session
                ->deposits()
                ->where(
                    'status',
                    'Posted'
                )
                ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | Available Cash
        |--------------------------------------------------------------------------
        */

        return max(
            0,
            $openingCash
            + $cashSales
            - $cashDeposit
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lock Deposit
    |--------------------------------------------------------------------------
    */

    protected function lockDeposit(
        CashierDeposit $deposit
    ): CashierDeposit {
        return CashierDeposit::query()
            ->lockForUpdate()
            ->findOrFail(
                $deposit->id
            );
    }

    /*
    |-----------------------------------------------------------------------
    | Validate User Context
    |-----------------------------------------------------------------------
    */

    protected function validateUserContext(
        User $user,
        CashierDeposit $deposit
    ): void {
        if (
            (int) $deposit->company_id !==
            (int) $user->company_id
        ) {
            throw ValidationException::withMessages([
                'deposit' =>
                    'Deposit tidak sesuai dengan company user.',
            ]);
        }

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
    }
}