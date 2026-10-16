<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountMapping;
use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\BranchAccountMapping;
use App\Models\Accounting\FundTransferHeader;
use App\Models\Accounting\FiscalYear;
use App\Models\MasterData\Branch;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\ChartOfAccount;
use RuntimeException;

class FundTransferService
{
    public function __construct(
        protected CodeGeneratorService $codeGeneratorService,
        protected JournalEntryService $journalEntryService,
        protected DocumentActivityService $documentActivityService,
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(array $data): FundTransferHeader
    {
        return DB::transaction(function () use ($data) {
            $user = auth()->user();

            if (! $user) {
                throw new RuntimeException(
                    'Authenticated user is required.'
                );
            }

            $companyId = (int) $user->company_id;

            if (! $companyId) {
                throw new RuntimeException(
                    'User company is not configured.'
                );
            }

            $this->validateTransferData($companyId, $data);

            $fundTransfer = FundTransferHeader::create([
                'company_id' => $companyId,
                'transfer_number' => $this->codeGeneratorService
                    ->next('fund_transfer'),
                'transfer_date' => $data['transfer_date'],
                'from_branch_id' => $data['from_branch_id'] ?? null,
                'from_account_id' => $data['from_account_id'],
                'to_branch_id' => $data['to_branch_id'] ?? null,
                'to_account_id' => $data['to_account_id'],
                'amount' => round((float) $data['amount'], 2),
                'description' => $data['description'] ?? null,
                'transfer_mode' => $data['transfer_mode'] ?? 'receivable',
                'status' => 'Draft',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->documentActivityService->record(
                $fundTransfer,
                'CREATED',
                null,
                'Draft',
                'Fund transfer created.'
            );

            return $fundTransfer;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        FundTransferHeader $fundTransfer,
        array $data
    ): FundTransferHeader {
        return DB::transaction(function () use (
            $fundTransfer,
            $data
        ) {
            $fundTransfer = FundTransferHeader::query()
                ->lockForUpdate()
                ->findOrFail($fundTransfer->id);

            if (! in_array(
                $fundTransfer->status,
                ['Draft', 'Rejected'],
                true
            )) {
                throw new RuntimeException(
                    'Only Draft or Rejected fund transfer can be edited.'
                );
            }

            $companyId = (int) $fundTransfer->company_id;

            $this->validateTransferData($companyId, $data);

            $oldStatus = $fundTransfer->status;

            $fundTransfer->update([
                'transfer_date' => $data['transfer_date'],
                'from_branch_id' => $data['from_branch_id'] ?? null,
                'from_account_id' => $data['from_account_id'],
                'to_branch_id' => $data['to_branch_id'] ?? null,
                'to_account_id' => $data['to_account_id'],
                'amount' => round((float) $data['amount'], 2),
                'description' => $data['description'] ?? null,
                'transfer_mode' => $data['transfer_mode']
                    ?? $fundTransfer->transfer_mode
                    ?? 'receivable',
                'updated_by' => auth()->id(),
            ]);

            if ($oldStatus === 'Rejected') {
                $fundTransfer->update([
                    'status' => 'Draft',
                    'submitted_at' => null,
                    'submitted_by' => null,
                    'approved_at' => null,
                    'approved_by' => null,
                    'rejected_at' => null,
                    'rejected_by' => null,
                    'rejected_reason' => null,
                ]);

                $this->documentActivityService->record(
                    $fundTransfer,
                    'RESUBMITTED',
                    'Rejected',
                    'Draft',
                    'Rejected fund transfer was corrected.'
                );
            } else {
                $this->documentActivityService->record(
                    $fundTransfer,
                    'UPDATED',
                    'Draft',
                    'Draft',
                    'Fund transfer updated.'
                );
            }

            return $fundTransfer->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        FundTransferHeader $fundTransfer
    ): FundTransferHeader {
        return DB::transaction(function () use ($fundTransfer) {
            $fundTransfer = FundTransferHeader::query()
                ->lockForUpdate()
                ->findOrFail($fundTransfer->id);

            if (! in_array(
                $fundTransfer->status,
                ['Draft', 'Rejected'],
                true
            )) {
                throw new RuntimeException(
                    'Only Draft or Rejected fund transfer can be submitted.'
                );
            }

            $oldStatus = $fundTransfer->status;

            $fundTransfer->update([
                'status' => 'Submitted',
                'submitted_at' => now(),
                'submitted_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $fundTransfer,
                'SUBMITTED',
                $oldStatus,
                'Submitted',
                'Fund transfer submitted for approval.'
            );

            return $fundTransfer->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        FundTransferHeader $fundTransfer
    ): FundTransferHeader {
        return DB::transaction(function () use ($fundTransfer) {
            $fundTransfer = FundTransferHeader::query()
                ->lockForUpdate()
                ->findOrFail($fundTransfer->id);

            if ($fundTransfer->status !== 'Submitted') {
                throw new RuntimeException(
                    'Only Submitted fund transfer can be approved.'
                );
            }

            $fundTransfer->update([
                'status' => 'Approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $fundTransfer,
                'APPROVED',
                'Submitted',
                'Approved',
                'Fund transfer approved.'
            );

            return $fundTransfer->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        FundTransferHeader $fundTransfer,
        string $reason
    ): FundTransferHeader {
        return DB::transaction(function () use (
            $fundTransfer,
            $reason
        ) {
            $fundTransfer = FundTransferHeader::query()
                ->lockForUpdate()
                ->findOrFail($fundTransfer->id);

            if ($fundTransfer->status !== 'Submitted') {
                throw new RuntimeException(
                    'Only Submitted fund transfer can be rejected.'
                );
            }

            $reason = trim($reason);

            if ($reason === '') {
                throw new RuntimeException(
                    'Rejection reason is required.'
                );
            }

            $fundTransfer->update([
                'status' => 'Rejected',
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'rejected_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $fundTransfer,
                'REJECTED',
                'Submitted',
                'Rejected',
                $reason
            );

            return $fundTransfer->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function post(
        FundTransferHeader $fundTransfer
    ): FundTransferHeader {
        return DB::transaction(function () use ($fundTransfer) {
            $fundTransfer = FundTransferHeader::query()
                ->lockForUpdate()
                ->findOrFail($fundTransfer->id);

            if ($fundTransfer->status !== 'Approved') {
                throw new RuntimeException(
                    'Only Approved fund transfer can be posted.'
                );
            }

            if ($fundTransfer->journal_entry_id) {
                throw new RuntimeException(
                    'Fund transfer already has a journal entry.'
                );
            }

            $user = auth()->user();

            if (! $user) {
                throw new RuntimeException(
                    'Authenticated user is required.'
                );
            }

            $companyId = (int) $fundTransfer->company_id;

            $amount = round((float) $fundTransfer->amount, 2);

            if ($amount <= 0) {
                throw new RuntimeException(
                    'Fund transfer amount must be greater than zero.'
                );
            }

            $fromBranchId = $fundTransfer->from_branch_id
                ? (int) $fundTransfer->from_branch_id
                : null;

            $toBranchId = $fundTransfer->to_branch_id
                ? (int) $fundTransfer->to_branch_id
                : null;

            if ($fromBranchId === null && $toBranchId === null) {
                throw new RuntimeException(
                    'At least one side of the transfer must belong to a branch.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Branches
            |--------------------------------------------------------------------------
            */

            foreach ([$fromBranchId, $toBranchId] as $branchId) {
                if ($branchId === null) {
                    continue;
                }

                Branch::query()
                    ->where('company_id', $companyId)
                    ->findOrFail($branchId);
            }

            /*
            |--------------------------------------------------------------------------
            | Resolve Account
            |--------------------------------------------------------------------------
            */

            $resolveAccount = function (
                ?int $branchId,
                int $accountId,
                string $side
            ) use ($companyId) {
                if ($branchId !== null) {
                    $mapping = BranchAccountMapping::query()
                        ->with('account')
                        ->where('company_id', $companyId)
                        ->where('branch_id', $branchId)
                        ->where('account_id', $accountId)
                        ->where('is_active', true)
                        ->where(function ($query) {
                            $query->where('key', 'cash_coh')
                                ->orWhere('key', 'like', 'cash_bank_%');
                        })
                        ->first();

                    if (! $mapping || ! $mapping->account) {
                        throw new RuntimeException(
                            "{$side} account is not mapped to the selected branch."
                        );
                    }

                    return $mapping->account;
                }

                $mapping = AccountMapping::query()
                    ->with('account')
                    ->where('company_id', $companyId)
                    ->where('key', 'company_cash')
                    ->where('account_id', $accountId)
                    ->where('is_active', true)
                    ->first();

                if (! $mapping || ! $mapping->account) {
                    throw new RuntimeException(
                        "{$side} account is not mapped as Company Cash."
                    );
                }

                return $mapping->account;
            };

            $fromAccount = $resolveAccount(
                $fromBranchId,
                (int) $fundTransfer->from_account_id,
                'Source'
            );

            $toAccount = $resolveAccount(
                $toBranchId,
                (int) $fundTransfer->to_account_id,
                'Destination'
            );

            /*
            |--------------------------------------------------------------------------
            | Validate Posting Accounts
            |--------------------------------------------------------------------------
            */

            foreach ([$fromAccount, $toAccount] as $account) {
                if (
                    (int) $account->company_id !== $companyId
                    || ! $account->is_posting
                    || ! $account->status
                ) {
                    throw new RuntimeException(
                        'One or more accounts are inactive or invalid.'
                    );
                }
            }

            if (
                (int) $fromAccount->id === (int) $toAccount->id
                && $fromBranchId === $toBranchId
            ) {
                throw new RuntimeException(
                    'Source and destination accounts cannot be identical in the same context.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Resolve Journal Context
            |--------------------------------------------------------------------------
            */

            $journalBranchId = $fromBranchId ?? $toBranchId;

            $journalBranch = Branch::query()
                ->where('company_id', $companyId)
                ->findOrFail($journalBranchId);

            $fiscalYear = FiscalYear::query()
                ->where('company_id', $companyId)
                ->where('status', 'Open')
                ->whereDate('start_date', '<=', $fundTransfer->transfer_date)
                ->whereDate('end_date', '>=', $fundTransfer->transfer_date)
                ->first();

            if (! $fiscalYear) {
                throw new RuntimeException(
                    'No open fiscal year found for the transfer date.'
                );
            }

            $accountingPeriod = AccountingPeriod::query()
                ->where('company_id', $companyId)
                ->where('fiscal_year_id', $fiscalYear->id)
                ->where('status', 'Open')
                ->whereDate('start_date', '<=', $fundTransfer->transfer_date)
                ->whereDate('end_date', '>=', $fundTransfer->transfer_date)
                ->first();

            if (! $accountingPeriod) {
                throw new RuntimeException(
                    'No open accounting period found for the transfer date.'
                );
            }

            $accountingJournal = AccountingJournal::query()
                ->where('company_id', $companyId)
                ->where('code', 'GEN')
                ->where('is_active', true)
                ->first();

            if (! $accountingJournal) {
                throw new RuntimeException(
                    'General Journal (GEN) is not configured for this company.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Build Journal Lines
            |--------------------------------------------------------------------------
            */

            $transferMode = $fundTransfer->transfer_mode ?: 'receivable';

            if (! in_array($transferMode, ['receivable', 'allocation'], true)) {
                throw new RuntimeException(
                    'Invalid fund transfer mode.'
                );
            }

            $lines = [];

            $sameBranchTransfer = $fromBranchId !== null
                && $fromBranchId === $toBranchId;

            /*
            |--------------------------------------------------------------------------
            | Same Branch Transfer
            |--------------------------------------------------------------------------
            |
            | Perpindahan antar-rekening dalam cabang yang sama.
            | Tidak membutuhkan akun penyeimbang antar-konteks.
            |--------------------------------------------------------------------------
            */

            if ($sameBranchTransfer) {
                $lines = [
                    [
                        'account_id' => $toAccount->id,
                        'branch_id' => $toBranchId,
                        'description' => 'Fund transfer destination',
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $fromAccount->id,
                        'branch_id' => $fromBranchId,
                        'description' => 'Fund transfer source',
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                ];
            } else {
                /*
                |--------------------------------------------------------------------------
                | Resolve Inter-Context Accounts
                |--------------------------------------------------------------------------
                */

                if ($transferMode === 'receivable') {
                    $sourceCounterpartCode = '110205';
                    $destinationCounterpartCode = '210105';
                } else {
                    $sourceCounterpartCode = '310501';
                    $destinationCounterpartCode = '310502';
                }

                $resolveCounterpart = function (
                    string $code,
                    string $side
                ) use ($companyId) {
                    $account = ChartOfAccount::query()
                        ->where('company_id', $companyId)
                        ->where('code', $code)
                        ->where('is_posting', true)
                        ->where('status', true)
                        ->first();

                    if (! $account) {
                        throw new RuntimeException(
                            "{$side} counterpart account {$code} is missing or inactive."
                        );
                    }

                    return $account;
                };

                $sourceCounterpart = $resolveCounterpart(
                    $sourceCounterpartCode,
                    'Source'
                );

                $destinationCounterpart = $resolveCounterpart(
                    $destinationCounterpartCode,
                    'Destination'
                );

                /*
                |--------------------------------------------------------------------------
                | Source Context
                |--------------------------------------------------------------------------
                */

                $lines[] = [
                    'account_id' => $sourceCounterpart->id,
                    'branch_id' => $fromBranchId,
                    'description' => $transferMode === 'receivable'
                        ? 'Inter-context receivable'
                        : 'Inter-context allocation out',
                    'debit' => $amount,
                    'credit' => 0,
                ];

                $lines[] = [
                    'account_id' => $fromAccount->id,
                    'branch_id' => $fromBranchId,
                    'description' => 'Fund transfer source',
                    'debit' => 0,
                    'credit' => $amount,
                ];

                /*
                |--------------------------------------------------------------------------
                | Destination Context
                |--------------------------------------------------------------------------
                */

                $lines[] = [
                    'account_id' => $toAccount->id,
                    'branch_id' => $toBranchId,
                    'description' => 'Fund transfer destination',
                    'debit' => $amount,
                    'credit' => 0,
                ];

                $lines[] = [
                    'account_id' => $destinationCounterpart->id,
                    'branch_id' => $toBranchId,
                    'description' => $transferMode === 'receivable'
                        ? 'Inter-context payable'
                        : 'Inter-context allocation in',
                    'debit' => 0,
                    'credit' => $amount,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Create Journal Entry
            |--------------------------------------------------------------------------
            */

            $journalEntry = $this->journalEntryService->create([
                'branch_id' => $journalBranch->id,
                'accounting_journal_id' => $accountingJournal->id,
                'fiscal_year_id' => $fiscalYear->id,
                'accounting_period_id' => $accountingPeriod->id,
                'entry_date' => $fundTransfer->transfer_date,
                'reference' => $fundTransfer->transfer_number,
                'description' => $fundTransfer->description
                    ?: 'Fund transfer ' . $fundTransfer->transfer_number,
                'lines' => $lines,
            ]);

            $this->journalEntryService->post($journalEntry);

            $oldStatus = $fundTransfer->status;

            $fundTransfer->update([
                'journal_entry_id' => $journalEntry->id,
                'status' => 'Posted',
                'posted_at' => now(),
                'posted_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->documentActivityService->record(
                $fundTransfer,
                'POSTED',
                $oldStatus,
                'Posted',
                'Fund transfer posted. Journal: ' . $journalEntry->code
            );

            return $fundTransfer->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        FundTransferHeader $fundTransfer,
        string $reason
    ): FundTransferHeader {
        return DB::transaction(function () use (
            $fundTransfer,
            $reason
        ) {
            $fundTransfer = FundTransferHeader::query()
                ->lockForUpdate()
                ->findOrFail($fundTransfer->id);

            if (! in_array(
                $fundTransfer->status,
                ['Draft', 'Submitted', 'Approved', 'Rejected'],
                true
            )) {
                throw new RuntimeException(
                    'Fund transfer cannot be cancelled from the current status.'
                );
            }

            $reason = trim($reason);

            if ($reason === '') {
                throw new RuntimeException(
                    'Cancellation reason is required.'
                );
            }

            $oldStatus = $fundTransfer->status;

            $fundTransfer->update([
                'status' => 'Cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancelled_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $fundTransfer,
                'CANCELLED',
                $oldStatus,
                'Cancelled',
                $reason
            );

            return $fundTransfer->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        FundTransferHeader $fundTransfer
    ): void {
        DB::transaction(function () use ($fundTransfer) {
            $fundTransfer = FundTransferHeader::query()
                ->lockForUpdate()
                ->findOrFail($fundTransfer->id);

            if (! in_array(
                $fundTransfer->status,
                ['Draft', 'Rejected', 'Cancelled'],
                true
            )) {
                throw new RuntimeException(
                    'Only Draft, Rejected, or Cancelled fund transfer can be deleted.'
                );
            }

            if ($fundTransfer->journal_entry_id !== null) {
                throw new RuntimeException(
                    'Fund transfer linked to a journal entry cannot be deleted.'
                );
            }

            $oldStatus = $fundTransfer->status;

            $this->documentActivityService->record(
                $fundTransfer,
                'DELETED',
                $oldStatus,
                null,
                'Fund transfer deleted.'
            );

            $fundTransfer->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Transfer Data
    |--------------------------------------------------------------------------
    */

    private function validateTransferData(
        int $companyId,
        array $data
    ): void {
        $fromBranchId = isset($data['from_branch_id'])
            ? (int) $data['from_branch_id']
            : null;

        $toBranchId = isset($data['to_branch_id'])
            ? (int) $data['to_branch_id']
            : null;

        if ($fromBranchId === null && $toBranchId === null) {
            throw new RuntimeException(
                'At least one side of the transfer must belong to a branch.'
            );
        }

        foreach ([$fromBranchId, $toBranchId] as $branchId) {
            if ($branchId === null) {
                continue;
            }

            Branch::query()
                ->where('company_id', $companyId)
                ->findOrFail($branchId);
        }

        $amount = round((float) ($data['amount'] ?? 0), 2);

        if ($amount <= 0) {
            throw new RuntimeException(
                'Fund transfer amount must be greater than zero.'
            );
        }

        $resolveAccount = function (
            ?int $branchId,
            int $accountId,
            string $side
        ) use ($companyId) {
            if ($branchId !== null) {
                $mapping = BranchAccountMapping::query()
                    ->with('account')
                    ->where('company_id', $companyId)
                    ->where('branch_id', $branchId)
                    ->where('account_id', $accountId)
                    ->where('is_active', true)
                    ->where(function ($query) {
                        $query->where('key', 'cash_coh')
                            ->orWhere('key', 'like', 'cash_bank_%');
                    })
                    ->first();
            } else {
                $mapping = AccountMapping::query()
                    ->with('account')
                    ->where('company_id', $companyId)
                    ->where('key', 'company_cash')
                    ->where('account_id', $accountId)
                    ->where('is_active', true)
                    ->first();
            }

            if (! $mapping || ! $mapping->account) {
                throw new RuntimeException(
                    "{$side} account is not mapped to the selected context."
                );
            }

            $account = $mapping->account;

            if (
                (int) $account->company_id !== $companyId
                || ! $account->is_posting
                || ! $account->status
            ) {
                throw new RuntimeException(
                    "{$side} account is inactive or invalid."
                );
            }

            return $account;
        };

        $fromAccount = $resolveAccount(
            $fromBranchId,
            (int) ($data['from_account_id'] ?? 0),
            'Source'
        );

        $toAccount = $resolveAccount(
            $toBranchId,
            (int) ($data['to_account_id'] ?? 0),
            'Destination'
        );

        if (
            (int) $fromAccount->id === (int) $toAccount->id
            && $fromBranchId === $toBranchId
        ) {
            throw new RuntimeException(
                'Source and destination accounts cannot be identical in the same context.'
            );
        }
    }
}