<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\PurchaseInvoiceHeader;
use App\Models\Purchasing\PurchasePaymentHeader;
use App\Services\Accounting\AccountMappingService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;

use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalYear;

use Illuminate\Support\Facades\DB;
use RuntimeException;



class PurchasePaymentService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected DocumentActivityService $documentActivityService;

    protected AccountMappingService $accountMappingService;

    protected JournalEntryService $journalEntryService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService,
        DocumentActivityService $documentActivityService,
        AccountMappingService $accountMappingService,
        JournalEntryService $journalEntryService
    ) {
        $this->codeGeneratorService = $codeGeneratorService;
        $this->documentActivityService = $documentActivityService;
        $this->accountMappingService = $accountMappingService;
        $this->journalEntryService = $journalEntryService;
    }

    /**
     * Create Purchase Payment.
     */
    public function create(array $data): PurchasePaymentHeader
    {
        return DB::transaction(function () use ($data) {

            $details = $data['details'] ?? [];

            if (empty($details)) {
                throw new RuntimeException(
                    'Purchase Payment must contain at least one invoice.'
                );
            }

            $paymentDate = $data['payment_date'] ?? null;

            if (!$paymentDate) {
                throw new RuntimeException(
                    'Payment date is required.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Load invoices
            |--------------------------------------------------------------------------
            */

            $invoiceIds = collect($details)
                ->pluck('purchase_invoice_header_id')
                ->filter()
                ->unique()
                ->values();

            if ($invoiceIds->isEmpty()) {
                throw new RuntimeException(
                    'No purchase invoice selected.'
                );
            }

            $invoices = PurchaseInvoiceHeader::query()
                ->whereIn('id', $invoiceIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($invoices->count() !== $invoiceIds->count()) {
                throw new RuntimeException(
                    'One or more purchase invoices were not found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Company / Branch / Supplier
            |--------------------------------------------------------------------------
            */

            $supplierId = null;
            $companyId = null;
            $branchId = null;

            foreach ($invoices as $invoice) {

                if (!in_array($invoice->status, [
                    'Posted',
                    'Partially Paid',
                ], true)) {
                    throw new RuntimeException(
                        "Purchase Invoice {$invoice->invoice_number} "
                        . "is not available for payment."
                    );
                }

                if ((float) $invoice->outstanding_amount <= 0) {
                    throw new RuntimeException(
                        "Purchase Invoice {$invoice->invoice_number} "
                        . "has no outstanding balance."
                    );
                }

                if ($supplierId === null) {
                    $supplierId = $invoice->supplier_id;
                } elseif ((int) $supplierId !== (int) $invoice->supplier_id) {
                    throw new RuntimeException(
                        'All purchase invoices must belong to the same supplier.'
                    );
                }

                if ($companyId === null) {
                    $companyId = $invoice->company_id;
                } elseif ((int) $companyId !== (int) $invoice->company_id) {
                    throw new RuntimeException(
                        'All purchase invoices must belong to the same company.'
                    );
                }

                if ($branchId === null) {
                    $branchId = $invoice->branch_id;
                } elseif ((int) $branchId !== (int) $invoice->branch_id) {
                    throw new RuntimeException(
                        'All purchase invoices must belong to the same branch.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Validate supplied company / branch / supplier
            |--------------------------------------------------------------------------
            */

            if (
                isset($data['company_id'])
                && (int) $data['company_id'] !== (int) $companyId
            ) {
                throw new RuntimeException(
                    'Selected company does not match the purchase invoices.'
                );
            }

            if (
                isset($data['branch_id'])
                && (int) $data['branch_id'] !== (int) $branchId
            ) {
                throw new RuntimeException(
                    'Selected branch does not match the purchase invoices.'
                );
            }

            if (
                isset($data['supplier_id'])
                && (int) $data['supplier_id'] !== (int) $supplierId
            ) {
                throw new RuntimeException(
                    'Selected supplier does not match the purchase invoices.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validate payment account
            |--------------------------------------------------------------------------
            */

            $paymentAccount = ChartOfAccount::query()
                ->where('id', $data['payment_account_id'] ?? 0)
                ->where('company_id', $companyId)
                ->where('status', true)
                ->where('is_posting', true)
                ->first();

            if (!$paymentAccount) {
                throw new RuntimeException(
                    'Payment account is invalid or inactive.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Prepare details
            |--------------------------------------------------------------------------
            */

            $paymentDetails = [];

            $totalAmount = 0;

            foreach ($details as $detail) {

                $invoiceId = $detail['purchase_invoice_header_id'] ?? null;

                if (!$invoiceId || !$invoices->has($invoiceId)) {
                    throw new RuntimeException(
                        'Invalid purchase invoice selected.'
                    );
                }

                $invoice = $invoices->get($invoiceId);

                $paymentAmount = round(
                    (float) ($detail['payment_amount'] ?? 0),
                    2
                );

                if ($paymentAmount <= 0) {
                    throw new RuntimeException(
                        "Payment amount for invoice "
                        . "{$invoice->invoice_number} must be greater than zero."
                    );
                }

                $outstanding = round(
                    (float) $invoice->outstanding_amount,
                    2
                );

                if ($paymentAmount > $outstanding) {
                    throw new RuntimeException(
                        "Payment amount for invoice "
                        . "{$invoice->invoice_number} "
                        . "cannot exceed outstanding amount "
                        . number_format($outstanding, 2, '.', ',') . '.'
                    );
                }

                $paymentDetails[] = [
                    'purchase_invoice_header_id' =>
                        $invoice->id,

                    'invoice_amount' =>
                        round((float) $invoice->grand_total, 2),

                    'previous_paid_amount' =>
                        round((float) $invoice->paid_amount, 2),

                    'previous_outstanding_amount' =>
                        $outstanding,

                    'payment_amount' =>
                        $paymentAmount,

                    'remarks' =>
                        $detail['remarks'] ?? null,
                ];

                $totalAmount += $paymentAmount;
            }

            $totalAmount = round($totalAmount, 2);

            if ($totalAmount <= 0) {
                throw new RuntimeException(
                    'Purchase Payment total must be greater than zero.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Create Header
            |--------------------------------------------------------------------------
            */

            $number = $this->codeGeneratorService->next(
                'purchase_payment'
            );

            $payment = PurchasePaymentHeader::create([
                'company_id' =>
                    $companyId,

                'branch_id' =>
                    $branchId,

                'number' =>
                    $number,

                'payment_date' =>
                    $paymentDate,

                'supplier_id' =>
                    $supplierId,

                'payment_method' =>
                    $data['payment_method'] ?? null,

                'payment_account_id' =>
                    $paymentAccount->id,

                'total_amount' =>
                    $totalAmount,

                'status' =>
                    'Draft',

                'remarks' =>
                    $data['remarks'] ?? null,

                'created_by' =>
                    auth()->id(),

                'updated_by' =>
                    auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Details
            |--------------------------------------------------------------------------
            */

            foreach ($paymentDetails as $detail) {
                $payment->details()->create($detail);
            }

            /*
            |--------------------------------------------------------------------------
            | Activity
            |--------------------------------------------------------------------------
            */

            $this->documentActivityService->record(
                $payment,
                'CREATED',
                null,
                'Draft',
                'Purchase Payment created.'
            );

            return $payment->load('details');
        });
    }

    /**
     * Submit Purchase Payment.
     */
    public function submit(PurchasePaymentHeader $payment): PurchasePaymentHeader
    {
        return DB::transaction(function () use ($payment) {

            $payment->refresh();

            if ($payment->status !== 'Draft') {
                throw new RuntimeException(
                    'Only Draft Purchase Payment can be submitted.'
                );
            }

            if ((float) $payment->total_amount <= 0) {
                throw new RuntimeException(
                    'Purchase Payment total must be greater than zero.'
                );
            }

            if ($payment->details()->count() === 0) {
                throw new RuntimeException(
                    'Purchase Payment must contain at least one invoice.'
                );
            }

            $payment->update([
                'status' => 'Submitted',
                'submitted_at' => now(),
                'submitted_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

           $this->documentActivityService->record(
                $payment,
                'SUBMITTED',
                'Draft',
                'Submitted',
                'Purchase Payment submitted.'
            );

            return $payment->fresh('details');
        });
    }

    /**
     * Approve Purchase Payment.
     */
    public function approve(PurchasePaymentHeader $payment): PurchasePaymentHeader
    {
        return DB::transaction(function () use ($payment) {

            $payment->refresh();

            if ($payment->status !== 'Submitted') {
                throw new RuntimeException(
                    'Only Submitted Purchase Payment can be approved.'
                );
            }

            $payment->update([
                'status' => 'Approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

          $this->documentActivityService->record(
            $payment,
            'APPROVED',
            'Submitted',
            'Approved',
            'Purchase Payment approved.'
        );

            return $payment->fresh('details');
        });
    }

    /**
     * Post Purchase Payment.
     *
     * Accounting:
     * Dr Trade Payable
     * Cr Payment Account
     */
    public function post(PurchasePaymentHeader $payment): PurchasePaymentHeader
    {
        return DB::transaction(function () use ($payment) {

            $payment = PurchasePaymentHeader::query()
                ->with('details')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->status !== 'Approved') {
                throw new RuntimeException(
                    'Only Approved Purchase Payment can be posted.'
                );
            }

            if ($payment->details->isEmpty()) {
                throw new RuntimeException(
                    'Purchase Payment must contain at least one invoice.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validate payment account
            |--------------------------------------------------------------------------
            */

            $paymentAccount = ChartOfAccount::query()
                ->where('id', $payment->payment_account_id)
                ->where('company_id', $payment->company_id)
                ->where('status', true)
                ->where('is_posting', true)
                ->first();

            if (!$paymentAccount) {
                throw new RuntimeException(
                    'Payment account is invalid or inactive.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Resolve Trade Payable Account
            |--------------------------------------------------------------------------
            */

            $tradePayableAccount =
                $this->accountMappingService->getAccount(
                    $payment->company_id,
                    'trade_payable'
                );

            /*
            |--------------------------------------------------------------------------
            | Fiscal Year
            |--------------------------------------------------------------------------
            */

            $fiscalYear = FiscalYear::query()
                ->where('company_id', $payment->company_id)
                ->whereDate('start_date', '<=', $payment->payment_date)
                ->whereDate('end_date', '>=', $payment->payment_date)
                ->where('status', 'Open')
                ->first();

            if (!$fiscalYear) {
                throw new RuntimeException(
                    'Open fiscal year not found for payment date.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Accounting Period
            |--------------------------------------------------------------------------
            */

            $period = AccountingPeriod::query()
                ->where('company_id', $payment->company_id)
                ->where('fiscal_year_id', $fiscalYear->id)
                ->whereDate('start_date', '<=', $payment->payment_date)
                ->whereDate('end_date', '>=', $payment->payment_date)
                ->where('status', 'Open')
                ->first();

            if (!$period) {
                throw new RuntimeException(
                    'Open accounting period not found for payment date.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Accounting Journal
            |--------------------------------------------------------------------------
            |
            | Purchase Payment uses the existing Adjustment journal.
            | Do not use "status" here because accounting_journals
            | uses "is_active".
            |
            */

            $journal = AccountingJournal::query()
            ->where('company_id', $payment->company_id)
            ->where('code', 'ADJ')
            ->where('is_active', true)
            ->first();

            if (!$journal) {
                throw new RuntimeException(
                    'Active Adjustment accounting journal not found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Re-validate Invoices
            |--------------------------------------------------------------------------
            */

            $debitTotal = 0;

            $journalLines = [];

            foreach ($payment->details as $detail) {

                $invoice = PurchaseInvoiceHeader::query()
                    ->lockForUpdate()
                    ->find($detail->purchase_invoice_header_id);

                if (!$invoice) {
                    throw new RuntimeException(
                        'Purchase Invoice not found.'
                    );
                }

                if ((int) $invoice->company_id !== (int) $payment->company_id) {
                    throw new RuntimeException(
                        'Purchase Invoice company does not match payment.'
                    );
                }

                if ((int) $invoice->branch_id !== (int) $payment->branch_id) {
                    throw new RuntimeException(
                        'Purchase Invoice branch does not match payment.'
                    );
                }

                if ((int) $invoice->supplier_id !== (int) $payment->supplier_id) {
                    throw new RuntimeException(
                        'Purchase Invoice supplier does not match payment.'
                    );
                }

                if (!in_array($invoice->status, [
                    'Posted',
                    'Partially Paid',
                ], true)) {
                    throw new RuntimeException(
                        "Purchase Invoice {$invoice->invoice_number} "
                        . "is not available for payment."
                    );
                }

                $outstanding = round(
                    (float) $invoice->outstanding_amount,
                    2
                );

                $paymentAmount = round(
                    (float) $detail->payment_amount,
                    2
                );

                if ($outstanding <= 0) {
                    throw new RuntimeException(
                        "Purchase Invoice {$invoice->invoice_number} "
                        . "has no outstanding balance."
                    );
                }

                if ($paymentAmount <= 0) {
                    throw new RuntimeException(
                        "Payment amount for invoice "
                        . "{$invoice->invoice_number} "
                        . "must be greater than zero."
                    );
                }

                if ($paymentAmount > $outstanding) {
                    throw new RuntimeException(
                        "Payment amount for invoice "
                        . "{$invoice->invoice_number} "
                        . "exceeds outstanding balance."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Update Invoice
                |--------------------------------------------------------------------------
                */

                $newPaidAmount = round(
                    (float) $invoice->paid_amount
                    + $paymentAmount,
                    2
                );

                $newOutstandingAmount = round(
                    $outstanding - $paymentAmount,
                    2
                );

                $newStatus = $newOutstandingAmount <= 0
                    ? 'Paid'
                    : 'Partially Paid';

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'outstanding_amount' => $newOutstandingAmount,
                    'status' => $newStatus,
                    'updated_by' => auth()->id(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Aggregate Trade Payable
                |--------------------------------------------------------------------------
                */

                $debitTotal += $paymentAmount;
            }

            $debitTotal = round($debitTotal, 2);

            if ($debitTotal <= 0) {
                throw new RuntimeException(
                    'Purchase Payment total must be greater than zero.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Header Total
            |--------------------------------------------------------------------------
            */

            $headerTotal = round(
                (float) $payment->total_amount,
                2
            );

            if (abs($debitTotal - $headerTotal) > 0.01) {
                throw new RuntimeException(
                    'Purchase Payment total does not match detail total.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Journal Entry
            |--------------------------------------------------------------------------
            */
                $journalEntry = $this->journalEntryService->create([
                    'branch_id' =>
                        $payment->branch_id,

                    'accounting_journal_id' =>
                        $journal->id,

                    'fiscal_year_id' =>
                        $fiscalYear->id,

                    'accounting_period_id' =>
                        $period->id,

                    'entry_date' =>
                        $payment->payment_date,

                    'reference' =>
                        $payment->number,

                    'description' =>
                        'Purchase Payment ' . $payment->number,

                    'lines' => [

                        [
                            'account_id' =>
                                $tradePayableAccount->id,

                            'description' =>
                                'Trade Payable - ' .
                                $payment->number,

                            'debit' =>
                                $debitTotal,

                            'credit' =>
                                0,
                        ],

                        [
                            'account_id' =>
                                $paymentAccount->id,

                            'description' =>
                                'Payment - ' .
                                $payment->number,

                            'debit' =>
                                0,

                            'credit' =>
                                $debitTotal,
                        ],

                    ],
                ]);
            /*
            |--------------------------------------------------------------------------
            | Post Journal
            |--------------------------------------------------------------------------
            */

            $this->journalEntryService->post($journalEntry);

            /*
            |--------------------------------------------------------------------------
            | Mark Payment Posted
            |--------------------------------------------------------------------------
            */

            $payment->update([
                'status' => 'Posted',
                'posted_at' => now(),
                'posted_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

           $this->documentActivityService->record(
                $payment,
                'POSTED',
                'Approved',
                'Posted',
                'Purchase Payment posted.'
            );

            return $payment->fresh('details');
        });
    }

    /**
     * Cancel Purchase Payment.
     */
    public function cancel(
        PurchasePaymentHeader $payment,
        ?string $reason = null
    ): PurchasePaymentHeader {
        return DB::transaction(function () use ($payment, $reason) {

            $payment->refresh();

            if (in_array($payment->status, [
                'Posted',
                'Cancelled',
            ], true)) {
                throw new RuntimeException(
                    'Posted or already cancelled Purchase Payment cannot be cancelled.'
                );
            }

            $payment->update([
                'status' => 'Cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'cancel_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);

         $this->documentActivityService->record(
                $payment,
                'CANCELLED',
                $payment->getOriginal('status'),
                'Cancelled',
                'Purchase Payment cancelled.'
            );

            return $payment->fresh('details');
        });
    }

    /**
     * Update Purchase Payment.
     */
    public function update(
        PurchasePaymentHeader $payment,
        array $data
    ): PurchasePaymentHeader {
        return DB::transaction(function () use ($payment, $data) {

            $payment = PurchasePaymentHeader::query()
                ->with('details')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if (!in_array($payment->status, [
                'Draft',
                'Rejected',
            ], true)) {
                throw new RuntimeException(
                    'Only Draft or Rejected Purchase Payment can be updated.'
                );
            }

            $details = $data['details'] ?? [];

            if (empty($details)) {
                throw new RuntimeException(
                    'Purchase Payment must contain at least one invoice.'
                );
            }

            $paymentDate = $data['payment_date'] ?? null;

            if (!$paymentDate) {
                throw new RuntimeException(
                    'Payment date is required.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Load invoices
            |--------------------------------------------------------------------------
            */

            $invoiceIds = collect($details)
                ->pluck('purchase_invoice_header_id')
                ->filter()
                ->unique()
                ->values();

            if ($invoiceIds->isEmpty()) {
                throw new RuntimeException(
                    'No purchase invoice selected.'
                );
            }

            $invoices = PurchaseInvoiceHeader::query()
                ->whereIn('id', $invoiceIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($invoices->count() !== $invoiceIds->count()) {
                throw new RuntimeException(
                    'One or more purchase invoices were not found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Company / Branch / Supplier
            |--------------------------------------------------------------------------
            */

            $supplierId = null;
            $companyId = null;
            $branchId = null;

            foreach ($invoices as $invoice) {

                if (!in_array($invoice->status, [
                    'Posted',
                    'Partially Paid',
                ], true)) {
                    throw new RuntimeException(
                        "Purchase Invoice {$invoice->invoice_number} "
                        . "is not available for payment."
                    );
                }

                if ((float) $invoice->outstanding_amount <= 0) {
                    throw new RuntimeException(
                        "Purchase Invoice {$invoice->invoice_number} "
                        . "has no outstanding balance."
                    );
                }

                if ($supplierId === null) {
                    $supplierId = $invoice->supplier_id;
                } elseif ((int) $supplierId !== (int) $invoice->supplier_id) {
                    throw new RuntimeException(
                        'All purchase invoices must belong to the same supplier.'
                    );
                }

                if ($companyId === null) {
                    $companyId = $invoice->company_id;
                } elseif ((int) $companyId !== (int) $invoice->company_id) {
                    throw new RuntimeException(
                        'All purchase invoices must belong to the same company.'
                    );
                }

                if ($branchId === null) {
                    $branchId = $invoice->branch_id;
                } elseif ((int) $branchId !== (int) $invoice->branch_id) {
                    throw new RuntimeException(
                        'All purchase invoices must belong to the same branch.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Payment Header
            |--------------------------------------------------------------------------
            */

            if (
                isset($data['branch_id'])
                && (int) $data['branch_id'] !== (int) $branchId
            ) {
                throw new RuntimeException(
                    'Selected branch does not match the purchase invoices.'
                );
            }

            if (
                isset($data['supplier_id'])
                && (int) $data['supplier_id'] !== (int) $supplierId
            ) {
                throw new RuntimeException(
                    'Selected supplier does not match the purchase invoices.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Payment Account
            |--------------------------------------------------------------------------
            */

            $paymentAccount = ChartOfAccount::query()
                ->where(
                    'id',
                    $data['payment_account_id'] ?? 0
                )
                ->where(
                    'company_id',
                    $companyId
                )
                ->where(
                    'status',
                    true
                )
                ->where(
                    'is_posting',
                    true
                )
                ->first();

            if (!$paymentAccount) {
                throw new RuntimeException(
                    'Payment account is invalid or inactive.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Prepare Details
            |--------------------------------------------------------------------------
            */

            $paymentDetails = [];

            $totalAmount = 0;

            foreach ($details as $detail) {

                $invoiceId =
                    $detail['purchase_invoice_header_id'] ?? null;

                if (
                    !$invoiceId ||
                    !$invoices->has($invoiceId)
                ) {
                    throw new RuntimeException(
                        'Invalid purchase invoice selected.'
                    );
                }

                $invoice =
                    $invoices->get($invoiceId);

                $paymentAmount = round(
                    (float) (
                        $detail['payment_amount'] ?? 0
                    ),
                    2
                );

                if ($paymentAmount <= 0) {
                    throw new RuntimeException(
                        "Payment amount for invoice "
                        . "{$invoice->invoice_number} "
                        . "must be greater than zero."
                    );
                }

                $outstanding = round(
                    (float) $invoice->outstanding_amount,
                    2
                );

                if ($paymentAmount > $outstanding) {
                    throw new RuntimeException(
                        "Payment amount for invoice "
                        . "{$invoice->invoice_number} "
                        . "cannot exceed outstanding amount "
                        . number_format(
                            $outstanding,
                            2,
                            '.',
                            ','
                        ) . '.'
                    );
                }

                $paymentDetails[] = [

                    'purchase_invoice_header_id' =>
                        $invoice->id,

                    'invoice_amount' =>
                        round(
                            (float) $invoice->grand_total,
                            2
                        ),

                    'previous_paid_amount' =>
                        round(
                            (float) $invoice->paid_amount,
                            2
                        ),

                    'previous_outstanding_amount' =>
                        $outstanding,

                    'payment_amount' =>
                        $paymentAmount,

                    'remarks' =>
                        $detail['remarks'] ?? null,

                ];

                $totalAmount += $paymentAmount;
            }

            $totalAmount =
                round(
                    $totalAmount,
                    2
                );

            if ($totalAmount <= 0) {
                throw new RuntimeException(
                    'Purchase Payment total must be greater than zero.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update Header
            |--------------------------------------------------------------------------
            */

            $payment->update([

                'company_id' =>
                    $companyId,

                'branch_id' =>
                    $branchId,

                'payment_date' =>
                    $paymentDate,

                'supplier_id' =>
                    $supplierId,

                'payment_method' =>
                    $data['payment_method'] ?? null,

                'payment_account_id' =>
                    $paymentAccount->id,

                'total_amount' =>
                    $totalAmount,

                'remarks' =>
                    $data['remarks'] ?? null,

                'updated_by' =>
                    auth()->id(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | Replace Details
            |--------------------------------------------------------------------------
            */

            $payment->details()->delete();

            foreach ($paymentDetails as $detail) {
                $payment->details()->create($detail);
            }

            /*
            |--------------------------------------------------------------------------
            | Activity
            |--------------------------------------------------------------------------
            */

            $this->documentActivityService->record(
                $payment,
                'UPDATED',
                'Draft',
                'Draft',
                'Purchase Payment updated.'
            );

            return $payment->fresh('details');
        });
    }
   
   /**
     * Reject Purchase Payment.
     */
   
    public function reject(
        PurchasePaymentHeader $payment,
        string $reason
    ): PurchasePaymentHeader {
        return DB::transaction(function () use ($payment, $reason) {

            $payment->refresh();

            if ($payment->status !== 'Submitted') {
                throw new RuntimeException(
                    'Only Submitted Purchase Payment can be rejected.'
                );
            }

            $reason = trim($reason);

            if ($reason === '') {
                throw new RuntimeException(
                    'Rejection reason is required.'
                );
            }

            $payment->update([
                'status' => 'Rejected',
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
                'reject_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);

            $this->documentActivityService->record(
                $payment,
                'REJECTED',
                'Submitted',
                'Rejected',
                'Purchase Payment rejected.'
            );

            return $payment->fresh('details');
        });
    }
}