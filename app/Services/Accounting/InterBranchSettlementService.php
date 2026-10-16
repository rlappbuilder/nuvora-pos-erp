<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountMapping;
use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\BranchAccountMapping;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\FundTransferHeader;
use App\Models\Accounting\InterBranchSettlementHeader;
use App\Models\Accounting\JournalEntry;
use App\Models\MasterData\Branch;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InterBranchSettlementService
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

    public function create(array $data): InterBranchSettlementHeader
    {
        return DB::transaction(function () use ($data) {
            $user = auth()->user();

            if (! $user || ! $user->company_id) {
                throw new RuntimeException(
                    'Authenticated user company is required.'
                );
            }

            $companyId = (int) $user->company_id;

            $this->validateSettlementData($companyId, $data);

            $header = InterBranchSettlementHeader::create([
                'company_id' => $companyId,
                'settlement_number' => $this->codeGeneratorService
                    ->next('inter_branch_settlement'),
                'settlement_date' => $data['settlement_date'],
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id' => $data['to_branch_id'],
                'from_account_id' => $data['from_account_id'],
                'amount' => round((float) $data['amount'], 2),
                'description' => $data['description'] ?? null,
                'status' => 'Draft',
                'receipt_status' => 'Pending',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->syncDetails($header, $data['details']);

            $this->documentActivityService->record(
                $header,
                'CREATED',
                null,
                'Draft',
                'Inter-branch settlement created.'
            );

            return $header->fresh(['details']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        InterBranchSettlementHeader $settlement,
        array $data
    ): InterBranchSettlementHeader {
        return DB::transaction(function () use ($settlement, $data) {
            $settlement = InterBranchSettlementHeader::query()
                ->lockForUpdate()
                ->findOrFail($settlement->id);

            $this->assertCompanyAccess($settlement);

            if (! in_array(
                $settlement->status,
                ['Draft', 'Rejected'],
                true
            )) {
                throw new RuntimeException(
                    'Only Draft or Rejected settlement can be edited.'
                );
            }

            $companyId = (int) $settlement->company_id;

            $this->validateSettlementData(
                $companyId,
                $data,
                $settlement->id
            );

            $oldStatus = $settlement->status;

            $settlement->update([
                'settlement_date' => $data['settlement_date'],
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id' => $data['to_branch_id'],
                'from_account_id' => $data['from_account_id'],
                'amount' => round((float) $data['amount'], 2),
                'description' => $data['description'] ?? null,
                'updated_by' => auth()->id(),
            ]);

            $this->syncDetails($settlement, $data['details']);

            if ($oldStatus === 'Rejected') {
                $settlement->update([
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
                    $settlement,
                    'RESUBMITTED',
                    'Rejected',
                    'Draft',
                    'Rejected settlement was corrected.'
                );
            } else {
                $this->documentActivityService->record(
                    $settlement,
                    'UPDATED',
                    'Draft',
                    'Draft',
                    'Inter-branch settlement updated.'
                );
            }

            return $settlement->fresh(['details']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(
        InterBranchSettlementHeader $settlement
    ): InterBranchSettlementHeader {
        return DB::transaction(function () use ($settlement) {
            $settlement = InterBranchSettlementHeader::query()
                ->lockForUpdate()
                ->findOrFail($settlement->id);

            $this->assertCompanyAccess($settlement);

            if ($settlement->status !== 'Draft') {
                throw new RuntimeException(
                    'Only Draft settlement can be submitted.'
                );
            }

            if ($settlement->details()->count() === 0) {
                throw new RuntimeException(
                    'Settlement must contain at least one fund transfer.'
                );
            }

            $this->validateOutstandingDetails($settlement);

            $settlement->update([
                'status' => 'Submitted',
                'submitted_at' => now(),
                'submitted_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $settlement,
                'SUBMITTED',
                'Draft',
                'Submitted',
                'Inter-branch settlement submitted for approval.'
            );

            return $settlement->fresh(['details']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approve(
        InterBranchSettlementHeader $settlement
    ): InterBranchSettlementHeader {
        return DB::transaction(function () use ($settlement) {
            $settlement = InterBranchSettlementHeader::query()
                ->lockForUpdate()
                ->findOrFail($settlement->id);

            $this->assertCompanyAccess($settlement);

            if ($settlement->status !== 'Submitted') {
                throw new RuntimeException(
                    'Only Submitted settlement can be approved.'
                );
            }

            $this->validateOutstandingDetails($settlement);

            $settlement->update([
                'status' => 'Approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $settlement,
                'APPROVED',
                'Submitted',
                'Approved',
                'Inter-branch settlement approved.'
            );

            return $settlement->fresh(['details']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        InterBranchSettlementHeader $settlement,
        string $reason
    ): InterBranchSettlementHeader {
        return DB::transaction(function () use ($settlement, $reason) {
            $settlement = InterBranchSettlementHeader::query()
                ->lockForUpdate()
                ->findOrFail($settlement->id);

            $this->assertCompanyAccess($settlement);

            if ($settlement->status !== 'Submitted') {
                throw new RuntimeException(
                    'Only Submitted settlement can be rejected.'
                );
            }

            $reason = trim($reason);

            if ($reason === '') {
                throw new RuntimeException(
                    'Rejection reason is required.'
                );
            }

            $settlement->update([
                'status' => 'Rejected',
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'rejected_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $settlement,
                'REJECTED',
                'Submitted',
                'Rejected',
                $reason
            );

            return $settlement->fresh(['details']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Post Payment
    |--------------------------------------------------------------------------
    |
    | Jurnal hanya untuk cabang pembayar.
    |
    | Debit  : Utang Antar-Cabang (210104)
    | Kredit : Kas/Bank cabang pembayar
    |
    */

    public function post(
        InterBranchSettlementHeader $settlement
    ): InterBranchSettlementHeader {
        return DB::transaction(function () use ($settlement) {
            $settlement = InterBranchSettlementHeader::query()
                ->with('details')
                ->lockForUpdate()
                ->findOrFail($settlement->id);

            $this->assertCompanyAccess($settlement);

            if ($settlement->status !== 'Approved') {
                throw new RuntimeException(
                    'Only Approved settlement can be posted.'
                );
            }

            if ($settlement->payment_journal_entry_id) {
                throw new RuntimeException(
                    'Settlement payment journal already exists.'
                );
            }

            $this->validateOutstandingDetails($settlement);

            $companyId = (int) $settlement->company_id;
            $payerBranchId = (int) $settlement->from_branch_id;
            $amount = round((float) $settlement->amount, 2);

            $payerAccount = $this->resolveBranchCashAccount(
                $companyId,
                $payerBranchId,
                (int) $settlement->from_account_id,
                'Payment'
            );

            $payableAccount = $this->resolvePostingAccount(
                $companyId,
                '210105',
                'Inter-Branch Payable'
            );

            $journalEntry = $this->createJournalEntry(
                $companyId,
                $payerBranchId,
                $settlement->settlement_date,
                $settlement->settlement_number,
                $settlement->description
                    ?: 'Inter-branch settlement payment '
                        . $settlement->settlement_number,
                [
                    [
                        'account_id' => $payableAccount->id,
                        'branch_id' => $payerBranchId,
                        'description' => 'Settlement of inter-branch payable',
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $payerAccount->id,
                        'branch_id' => $payerBranchId,
                        'description' => 'Inter-branch settlement payment',
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                ]
            );

            $this->journalEntryService->post($journalEntry);

            $settlement->update([
                'payment_journal_entry_id' => $journalEntry->id,
                'status' => 'Posted',
                'receipt_status' => 'Pending',
                'posted_at' => now(),
                'posted_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $settlement,
                'POSTED',
                'Approved',
                'Posted',
                'Payment posted. Journal: ' . $journalEntry->code
            );

            return $settlement->fresh(['details']);
        });
    }

    /*
|--------------------------------------------------------------------------
| Receive Payment
|--------------------------------------------------------------------------
|
| Jurnal hanya untuk cabang penerima.
|
| Debit  : Kas/Bank cabang penerima
| Kredit : Piutang Antar-Cabang (110205)
|--------------------------------------------------------------------------
*/

public function receive(
    InterBranchSettlementHeader $settlement,
    int $toAccountId,
    string $receivedDate
): InterBranchSettlementHeader {
    return DB::transaction(function () use (
        $settlement,
        $toAccountId,
        $receivedDate
    ) {
        $settlement = InterBranchSettlementHeader::query()
            ->lockForUpdate()
            ->findOrFail($settlement->id);

        $this->assertCompanyAccess($settlement);

        if ($settlement->status !== 'Posted') {
            throw new RuntimeException(
                'Only Posted settlement can be received.'
            );
        }

        if ($settlement->receipt_status !== 'Pending') {
            throw new RuntimeException(
                'Settlement receipt is no longer pending.'
            );
        }

        if ($settlement->receipt_journal_entry_id) {
            throw new RuntimeException(
                'Settlement receipt journal already exists.'
            );
        }

        if (
            ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $receivedDate)
            || ! \DateTimeImmutable::createFromFormat('!Y-m-d', $receivedDate)
            || \DateTimeImmutable::createFromFormat('!Y-m-d', $receivedDate)->format('Y-m-d') !== $receivedDate
        ) {
            throw new RuntimeException(
                'Invalid receipt date.'
            );
        }

        $companyId = (int) $settlement->company_id;
        $recipientBranchId = (int) $settlement->to_branch_id;
        $amount = round((float) $settlement->amount, 2);

        $recipientAccount = $this->resolveBranchCashAccount(
            $companyId,
            $recipientBranchId,
            $toAccountId,
            'Receipt'
        );

        $receivableAccount = $this->resolvePostingAccount(
            $companyId,
            '110205',
            'Inter-Branch Receivable'
        );

        $journalEntry = $this->createJournalEntry(
            $companyId,
            $recipientBranchId,
            $receivedDate,
            $settlement->settlement_number,
            'Receipt of inter-branch settlement '
                . $settlement->settlement_number,
            [
                [
                    'account_id' => $recipientAccount->id,
                    'branch_id' => $recipientBranchId,
                    'description' => 'Inter-branch settlement receipt',
                    'debit' => $amount,
                    'credit' => 0,
                ],
                [
                    'account_id' => $receivableAccount->id,
                    'branch_id' => $recipientBranchId,
                    'description' => 'Settlement of inter-branch receivable',
                    'debit' => 0,
                    'credit' => $amount,
                ],
            ]
        );

        $this->journalEntryService->post($journalEntry);

        $settlement->update([
            'to_account_id' => $recipientAccount->id,
            'receipt_journal_entry_id' => $journalEntry->id,
            'receipt_status' => 'Received',
            'received_at' => now(),
            'received_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->documentActivityService->record(
            $settlement,
            'RECEIVED',
            'Pending',
            'Received',
            'Payment received on '
                . $receivedDate
                . '. Journal: '
                . $journalEntry->code
        );

        return $settlement->fresh(['details']);
    });
}

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        InterBranchSettlementHeader $settlement,
        string $reason
    ): InterBranchSettlementHeader {
        return DB::transaction(function () use ($settlement, $reason) {
            $settlement = InterBranchSettlementHeader::query()
                ->lockForUpdate()
                ->findOrFail($settlement->id);

            $this->assertCompanyAccess($settlement);

            if (! in_array(
                $settlement->status,
                ['Draft', 'Submitted', 'Approved', 'Rejected'],
                true
            )) {
                throw new RuntimeException(
                    'Settlement cannot be cancelled from its current status.'
                );
            }

            $reason = trim($reason);

            if ($reason === '') {
                throw new RuntimeException(
                    'Cancellation reason is required.'
                );
            }

            $oldStatus = $settlement->status;

            $settlement->update([
                'status' => 'Cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancelled_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $settlement,
                'CANCELLED',
                $oldStatus,
                'Cancelled',
                $reason
            );

            return $settlement->fresh(['details']);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        InterBranchSettlementHeader $settlement
    ): void {
        DB::transaction(function () use ($settlement) {
            $settlement = InterBranchSettlementHeader::query()
                ->lockForUpdate()
                ->findOrFail($settlement->id);

            $this->assertCompanyAccess($settlement);

            if (! in_array(
                $settlement->status,
                ['Draft', 'Rejected', 'Cancelled'],
                true
            )) {
                throw new RuntimeException(
                    'Only Draft, Rejected, or Cancelled settlement can be deleted.'
                );
            }

            if (
                $settlement->payment_journal_entry_id
                || $settlement->receipt_journal_entry_id
            ) {
                throw new RuntimeException(
                    'Settlement linked to a journal entry cannot be deleted.'
                );
            }

            $oldStatus = $settlement->status;

            $this->documentActivityService->record(
                $settlement,
                'DELETED',
                $oldStatus,
                null,
                'Inter-branch settlement deleted.'
            );

            $settlement->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Eligible Fund Transfers
    |--------------------------------------------------------------------------
    |
    | fromBranchId = cabang pembayar.
    | toBranchId   = cabang penerima.
    |
    | Pada Fund Transfer asal:
    | to_branch_id   = cabang pembayar.
    | from_branch_id = cabang penerima.
    |--------------------------------------------------------------------------
    */

    public function eligibleFundTransfers(
        int $companyId,
        int $fromBranchId,
        int $toBranchId
    ) {
        $transfers = FundTransferHeader::query()
            ->where('company_id', $companyId)
            ->where('status', 'Posted')
            ->where('transfer_mode', 'receivable')
            ->where('to_branch_id', $fromBranchId)
            ->where('from_branch_id', $toBranchId)
            ->orderBy('transfer_date')
            ->orderBy('id')
            ->get();

        return $transfers->map(function ($transfer) {
            $settledAmount = $this->settledAmountForTransfer(
                (int) $transfer->id
            );

            $outstanding = round(
                (float) $transfer->amount - $settledAmount,
                2
            );

            $transfer->settled_amount = $settledAmount;
            $transfer->outstanding_amount = max(0, $outstanding);

            return $transfer;
        })->filter(
            fn ($transfer) => $transfer->outstanding_amount > 0
        )->values();
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Settlement Data
    |--------------------------------------------------------------------------
    */

    private function validateSettlementData(
        int $companyId,
        array $data,
        ?int $excludeSettlementId = null
    ): void {
        $fromBranchId = (int) ($data['from_branch_id'] ?? 0);
        $toBranchId = (int) ($data['to_branch_id'] ?? 0);

        if (
            $fromBranchId <= 0
            || $toBranchId <= 0
            || $fromBranchId === $toBranchId
        ) {
            throw new RuntimeException(
                'Payment and recipient branches must be different.'
            );
        }

        Branch::query()
            ->where('company_id', $companyId)
            ->findOrFail($fromBranchId);

        Branch::query()
            ->where('company_id', $companyId)
            ->findOrFail($toBranchId);

        $amount = round((float) ($data['amount'] ?? 0), 2);

        if ($amount <= 0) {
            throw new RuntimeException(
                'Settlement amount must be greater than zero.'
            );
        }

        $this->resolveBranchCashAccount(
            $companyId,
            $fromBranchId,
            (int) ($data['from_account_id'] ?? 0),
            'Payment'
        );

        $details = collect($data['details'] ?? []);

        if ($details->isEmpty()) {
            throw new RuntimeException(
                'Settlement must contain at least one fund transfer.'
            );
        }

        $detailTotal = round(
            (float) $details->sum(
                fn ($detail) => (float) ($detail['amount'] ?? 0)
            ),
            2
        );

        if ($detailTotal !== $amount) {
            throw new RuntimeException(
                'Settlement header amount must equal the total detail amount.'
            );
        }

        $transferIds = $details
            ->pluck('fund_transfer_header_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($transferIds->count() !== $details->count()) {
            throw new RuntimeException(
                'A fund transfer cannot be selected more than once.'
            );
        }

        /*
         * Lock in ascending ID order to reduce deadlock risk.
         */
        $transfers = FundTransferHeader::query()
            ->where('company_id', $companyId)
            ->whereIn('id', $transferIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($transfers->count() !== $transferIds->count()) {
            throw new RuntimeException(
                'One or more fund transfers do not belong to this company.'
            );
        }

        foreach ($details as $detail) {
            $transferId = (int) $detail['fund_transfer_header_id'];
            $detailAmount = round((float) $detail['amount'], 2);
            $transfer = $transfers->get($transferId);

            if (
                $transfer->status !== 'Posted'
                || $transfer->transfer_mode !== 'receivable'
            ) {
                throw new RuntimeException(
                    "Fund transfer {$transfer->transfer_number} must be Posted and use receivable mode."
                );
            }

            if (
                (int) $transfer->to_branch_id !== $fromBranchId
                || (int) $transfer->from_branch_id !== $toBranchId
            ) {
                throw new RuntimeException(
                    "Fund transfer {$transfer->transfer_number} does not match the selected branch pair."
                );
            }

            if ($detailAmount <= 0) {
                throw new RuntimeException(
                    'Settlement detail amount must be greater than zero.'
                );
            }

            $settledAmount = $this->settledAmountForTransfer(
                $transferId,
                $excludeSettlementId
            );

            $outstanding = round(
                (float) $transfer->amount - $settledAmount,
                2
            );

            if ($detailAmount > $outstanding) {
                throw new RuntimeException(
                    "Settlement amount for fund transfer {$transfer->transfer_number} exceeds its outstanding balance of "
                    . number_format(max(0, $outstanding), 2, ',', '.')
                    . '.'
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Outstanding Before Workflow / Posting
    |--------------------------------------------------------------------------
    */

    private function validateOutstandingDetails(
        InterBranchSettlementHeader $settlement
    ): void {
        $details = $settlement->details()
            ->orderBy('fund_transfer_header_id')
            ->get();

        if ($details->isEmpty()) {
            throw new RuntimeException(
                'Settlement must contain at least one fund transfer.'
            );
        }

        $total = 0;

        foreach ($details as $detail) {
            $transfer = FundTransferHeader::query()
                ->where('company_id', $settlement->company_id)
                ->lockForUpdate()
                ->findOrFail($detail->fund_transfer_header_id);

            if (
                $transfer->status !== 'Posted'
                || $transfer->transfer_mode !== 'receivable'
            ) {
                throw new RuntimeException(
                    "Fund transfer {$transfer->transfer_number} is no longer eligible for settlement."
                );
            }

            if (
                (int) $transfer->to_branch_id
                    !== (int) $settlement->from_branch_id
                || (int) $transfer->from_branch_id
                    !== (int) $settlement->to_branch_id
            ) {
                throw new RuntimeException(
                    "Fund transfer {$transfer->transfer_number} does not match the settlement branches."
                );
            }

            $settledAmount = $this->settledAmountForTransfer(
                (int) $transfer->id,
                (int) $settlement->id
            );

            $outstanding = round(
                (float) $transfer->amount - $settledAmount,
                2
            );

            if ((float) $detail->amount > $outstanding) {
                throw new RuntimeException(
                    "Fund transfer {$transfer->transfer_number} no longer has sufficient outstanding balance."
                );
            }

            $total += (float) $detail->amount;
        }

        if (
            round($total, 2)
            !== round((float) $settlement->amount, 2)
        ) {
            throw new RuntimeException(
                'Settlement header and detail amounts do not match.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Posted Settlement Amount
    |--------------------------------------------------------------------------
    */

    private function settledAmountForTransfer(
        int $fundTransferId,
        ?int $excludeSettlementId = null
    ): float {
        $query = DB::table('inter_branch_settlement_details')
            ->join(
                'inter_branch_settlement_headers',
                'inter_branch_settlement_headers.id',
                '=',
                'inter_branch_settlement_details.inter_branch_settlement_header_id'
            )
            ->where(
                'inter_branch_settlement_details.fund_transfer_header_id',
                $fundTransferId
            )
            ->where(
                'inter_branch_settlement_headers.status',
                'Posted'
            )
            ->whereNull(
                'inter_branch_settlement_headers.deleted_at'
            );

        if ($excludeSettlementId !== null) {
            $query->where(
                'inter_branch_settlement_headers.id',
                '!=',
                $excludeSettlementId
            );
        }

        return round(
            (float) $query->sum(
                'inter_branch_settlement_details.amount'
            ),
            2
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Sync Details
    |--------------------------------------------------------------------------
    */

    private function syncDetails(
        InterBranchSettlementHeader $settlement,
        array $details
    ): void {
        $settlement->details()->delete();

        foreach ($details as $detail) {
            $settlement->details()->create([
                'fund_transfer_header_id' =>
                    (int) $detail['fund_transfer_header_id'],
                'amount' => round((float) $detail['amount'], 2),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Branch Cash / Bank Account
    |--------------------------------------------------------------------------
    */

    private function resolveBranchCashAccount(
        int $companyId,
        int $branchId,
        int $accountId,
        string $side
    ): ChartOfAccount {
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
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Posting Account by COA Code
    |--------------------------------------------------------------------------
    */

    private function resolvePostingAccount(
        int $companyId,
        string $code,
        string $label
    ): ChartOfAccount {
        $account = ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('code', $code)
            ->where('is_posting', true)
            ->where('status', true)
            ->first();

        if (! $account) {
            throw new RuntimeException(
                "{$label} account ({$code}) is missing or inactive."
            );
        }

        return $account;
    }

    /*
|--------------------------------------------------------------------------

Create Journal Entry
*/

private function createJournalEntry(
int $companyId,
int $branchId,
$entryDate,
string $reference,
string $description,
array $lines
): JournalEntry {
$branch = Branch::query()
->where('company_id', $companyId)
->findOrFail($branchId);

$fiscalYear = FiscalYear::query()
    ->where('company_id', $companyId)
    ->where('status', 'Open')
    ->whereDate('start_date', '<=', $entryDate)
    ->whereDate('end_date', '>=', $entryDate)
    ->first();

if (! $fiscalYear) {
    throw new RuntimeException(
        'No open fiscal year found for the settlement date.'
    );
}

$period = AccountingPeriod::query()
    ->where('company_id', $companyId)
    ->where('fiscal_year_id', $fiscalYear->id)
    ->where('status', 'Open')
    ->whereDate('start_date', '<=', $entryDate)
    ->whereDate('end_date', '>=', $entryDate)
    ->first();

if (! $period) {
    throw new RuntimeException(
        'No open accounting period found for the settlement date.'
    );
}

$journal = AccountingJournal::query()
    ->where('company_id', $companyId)
    ->where('code', 'GEN')
    ->where('is_active', true)
    ->first();

if (! $journal) {
    throw new RuntimeException(
        'General Journal (GEN) is not configured for this company.'
    );
}

return $this->journalEntryService->create([
    'branch_id' => $branch->id,
    'accounting_journal_id' => $journal->id,
    'fiscal_year_id' => $fiscalYear->id,
    'accounting_period_id' => $period->id,
    'entry_date' => $entryDate,
    'reference' => $reference,
    'description' => $description,
    'lines' => $lines,
]);

}

    /*
    |--------------------------------------------------------------------------
    | Company Access
    |--------------------------------------------------------------------------
    */

    private function assertCompanyAccess(
        InterBranchSettlementHeader $settlement
    ): void {
        $companyId = (int) (auth()->user()?->company_id ?? 0);

        if (
            $companyId <= 0
            || (int) $settlement->company_id !== $companyId
        ) {
            throw new RuntimeException(
                'You are not authorized to access this settlement.'
            );
        }
    }
}