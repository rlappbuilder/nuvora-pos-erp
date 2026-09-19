<?php

namespace App\Services\Consignment;

use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalYear;
use App\Models\MasterData\Branch;
use App\Models\Reseller\ConsignmentReceivable\ConsignmentReceivableHeader;
use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;
use App\Services\Accounting\AccountMappingService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ConsignmentReceivableService
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
        $this->codeGeneratorService =
            $codeGeneratorService;

        $this->documentActivityService =
            $documentActivityService;

        $this->accountMappingService =
            $accountMappingService;

        $this->journalEntryService =
            $journalEntryService;
    }

    /**
 * Create Consignment Receivable.
 */
public function create(
    array $data
): ConsignmentReceivableHeader {
    return DB::transaction(function () use ($data) {

        $details =
            $data['details'] ?? [];

        if (empty($details)) {
            throw new RuntimeException(
                'Consignment Receivable must contain at least one settlement.'
            );
        }

        $paymentDate =
            $data['payment_date'] ?? null;

        if (!$paymentDate) {
            throw new RuntimeException(
                'Payment date is required.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Load Settlements
        |--------------------------------------------------------------------------
        */

        $settlementIds = collect($details)
            ->pluck('settlement_header_id')
            ->filter()
            ->unique()
            ->values();

        if ($settlementIds->isEmpty()) {
            throw new RuntimeException(
                'No settlement selected.'
            );
        }

        $settlements =
            ConsignmentSettlementHeader::query()
                ->whereIn(
                    'id',
                    $settlementIds
                )
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

        if (
            $settlements->count()
            !== $settlementIds->count()
        ) {
            throw new RuntimeException(
                'One or more settlements were not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Company / Branch / Reseller
        |--------------------------------------------------------------------------
        */

        $companyId = null;
        $branchId = null;
        $resellerId = null;

        foreach ($settlements as $settlement) {

            if ($settlement->status !== 'Posted') {
                throw new RuntimeException(
                    "Settlement {$settlement->settlement_number} "
                    . "is not available for payment."
                );
            }

            $receivable =
                round(
                    (float) $settlement->receivable_amount,
                    2
                );

            if ($receivable <= 0) {
                throw new RuntimeException(
                    "Settlement {$settlement->settlement_number} "
                    . "has no receivable balance."
                );
            }

            if ($companyId === null) {
                $companyId =
                    $settlement->company_id;
            } elseif (
                (int) $companyId
                !== (int) $settlement->company_id
            ) {
                throw new RuntimeException(
                    'All settlements must belong to the same company.'
                );
            }

            if ($branchId === null) {
                $branchId =
                    $settlement->branch_id;
            } elseif (
                (int) $branchId
                !== (int) $settlement->branch_id
            ) {
                throw new RuntimeException(
                    'All settlements must belong to the same branch.'
                );
            }

            if ($resellerId === null) {
                $resellerId =
                    $settlement->reseller_id;
            } elseif (
                (int) $resellerId
                !== (int) $settlement->reseller_id
            ) {
                throw new RuntimeException(
                    'All settlements must belong to the same reseller.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Branch
        |--------------------------------------------------------------------------
        */

        if (
            isset($data['branch_id'])
            && (int) $data['branch_id']
            !== (int) $branchId
        ) {
            throw new RuntimeException(
                'Selected branch does not match the settlements.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Reseller
        |--------------------------------------------------------------------------
        */

        if (
            isset($data['reseller_id'])
            && (int) $data['reseller_id']
            !== (int) $resellerId
        ) {
            throw new RuntimeException(
                'Selected reseller does not match the settlements.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Payment Account
        |--------------------------------------------------------------------------
        */

        $paymentAccount =
            ChartOfAccount::query()
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

        $receivableDetails = [];

        $totalAmount = 0;

        foreach ($details as $detail) {

            $settlementId =
                $detail['settlement_header_id']
                ?? null;

            if (
                !$settlementId
                || !$settlements->has($settlementId)
            ) {
                throw new RuntimeException(
                    'Invalid settlement selected.'
                );
            }

            $settlement =
                $settlements->get(
                    $settlementId
                );

            /*
            |--------------------------------------------------------------------------
            | Calculate Previous Payments
            |--------------------------------------------------------------------------
            */

            $previousPaidAmount =
                (float) ConsignmentReceivableHeader::query()
                    ->where(
                        'reseller_id',
                        $resellerId
                    )
                    ->whereIn(
                        'status',
                        [
                            'Posted',
                        ]
                    )
                    ->whereHas(
                        'details',
                        function ($query) use (
                            $settlementId
                        ) {
                            $query->where(
                                'settlement_header_id',
                                $settlementId
                            );
                        }
                    )
                    ->withSum(
                        [
                            'details as settlement_payment_total'
                            => function ($query) use (
                                $settlementId
                            ) {
                                $query->where(
                                    'settlement_header_id',
                                    $settlementId
                                );
                            },
                        ],
                        'payment_amount'
                    )
                    ->get()
                    ->sum(
                        'settlement_payment_total'
                    );

            $previousPaidAmount =
                round(
                    $previousPaidAmount,
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | Settlement Receivable
            |--------------------------------------------------------------------------
            |
            | Actual AR balance.
            |
            */

            $settlementReceivable =
                round(
                    (float) $settlement->receivable_amount,
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | Settlement Amount
            |--------------------------------------------------------------------------
            |
            | Original Settlement Grand Total.
            |
            */

            $settlementAmount =
                round(
                    (float) $settlement->grand_total,
                    2
                );

            /*
            |--------------------------------------------------------------------------
            | Previous Outstanding
            |--------------------------------------------------------------------------
            |
            | Outstanding tetap berdasarkan receivable_amount,
            | bukan grand_total.
            |
            */

            $previousOutstandingAmount =
                round(
                    $settlementReceivable
                    - $previousPaidAmount,
                    2
                );

            if (
                $previousOutstandingAmount <= 0
            ) {
                throw new RuntimeException(
                    "Settlement {$settlement->settlement_number} "
                    . "has no outstanding receivable balance."
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Payment Amount
            |--------------------------------------------------------------------------
            */

            $paymentAmount =
                round(
                    (float) (
                        $detail['payment_amount']
                        ?? 0
                    ),
                    2
                );

            if ($paymentAmount <= 0) {
                throw new RuntimeException(
                    "Payment amount for settlement "
                    . "{$settlement->settlement_number} "
                    . "must be greater than zero."
                );
            }

            if (
                $paymentAmount
                > $previousOutstandingAmount
            ) {
                throw new RuntimeException(
                    "Payment amount for settlement "
                    . "{$settlement->settlement_number} "
                    . "cannot exceed outstanding amount "
                    . number_format(
                        $previousOutstandingAmount,
                        2,
                        '.',
                        ','
                    ) . '.'
                );
            }

            $receivableDetails[] = [

                'settlement_header_id' =>
                    $settlement->id,

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT
                |--------------------------------------------------------------------------
                | Display Settlement Amount = Grand Total
                |--------------------------------------------------------------------------
                */

                'settlement_amount' =>
                    $settlementAmount,

                'previous_paid_amount' =>
                    $previousPaidAmount,

                'previous_outstanding_amount' =>
                    $previousOutstandingAmount,

                'payment_amount' =>
                    $paymentAmount,

                'remarks' =>
                    $detail['remarks'] ?? null,
            ];

            $totalAmount +=
                $paymentAmount;
        }

        $totalAmount =
            round(
                $totalAmount,
                2
            );

        if ($totalAmount <= 0) {
            throw new RuntimeException(
                'Consignment Receivable total must be greater than zero.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Header
        |--------------------------------------------------------------------------
        */

        $number =
            $this->codeGeneratorService->next(
                'consignment_receivable'
            );

        $receivable =
            ConsignmentReceivableHeader::create([

                'company_id' =>
                    $companyId,

                'branch_id' =>
                    $branchId,

                'number' =>
                    $number,

                'payment_date' =>
                    $paymentDate,

                'reseller_id' =>
                    $resellerId,

                'payment_method' =>
                    $data['payment_method']
                    ?? null,

                'payment_account_id' =>
                    $paymentAccount->id,

                'total_amount' =>
                    $totalAmount,

                'status' =>
                    'Draft',

                'remarks' =>
                    $data['remarks']
                    ?? null,

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

        foreach (
            $receivableDetails
            as $detail
        ) {
            $receivable
                ->details()
                ->create($detail);
        }

        /*
        |--------------------------------------------------------------------------
        | Activity
        |--------------------------------------------------------------------------
        */

        $this->documentActivityService->record(
            $receivable,
            'CREATED',
            null,
            'Draft',
            'Consignment Receivable created.'
        );

        return $receivable->load(
            'details'
        );
    });
}

    /**
     * Submit Consignment Receivable.
     */
    public function submit(
        ConsignmentReceivableHeader $receivable
    ): ConsignmentReceivableHeader {
        return DB::transaction(
            function () use ($receivable) {

                $receivable->refresh();

                if (
                    $receivable->status !== 'Draft'
                ) {
                    throw new RuntimeException(
                        'Only Draft Consignment Receivable can be submitted.'
                    );
                }

                if (
                    (float) $receivable->total_amount
                    <= 0
                ) {
                    throw new RuntimeException(
                        'Consignment Receivable total must be greater than zero.'
                    );
                }

                if (
                    $receivable->details()->count()
                    === 0
                ) {
                    throw new RuntimeException(
                        'Consignment Receivable must contain at least one settlement.'
                    );
                }

                $receivable->update([

                    'status' =>
                        'Submitted',

                    'submitted_at' =>
                        now(),

                    'submitted_by' =>
                        auth()->id(),

                    'updated_by' =>
                        auth()->id(),
                ]);

                $this->documentActivityService->record(
                    $receivable,
                    'SUBMITTED',
                    'Draft',
                    'Submitted',
                    'Consignment Receivable submitted.'
                );

                return $receivable->fresh(
                    'details'
                );
            }
        );
    }

    /**
     * Approve Consignment Receivable.
     */
    public function approve(
        ConsignmentReceivableHeader $receivable
    ): ConsignmentReceivableHeader {
        return DB::transaction(
            function () use ($receivable) {

                $receivable->refresh();

                if (
                    $receivable->status
                    !== 'Submitted'
                ) {
                    throw new RuntimeException(
                        'Only Submitted Consignment Receivable can be approved.'
                    );
                }

                $receivable->update([

                    'status' =>
                        'Approved',

                    'approved_at' =>
                        now(),

                    'approved_by' =>
                        auth()->id(),

                    'updated_by' =>
                        auth()->id(),
                ]);

                $this->documentActivityService->record(
                    $receivable,
                    'APPROVED',
                    'Submitted',
                    'Approved',
                    'Consignment Receivable approved.'
                );

                return $receivable->fresh(
                    'details'
                );
            }
        );
    }

    /**
     * Post Consignment Receivable.
     *
     * Accounting:
     *
     * Dr Consignment Cash / Bank
     * Cr Settlement Receivable
     */
    public function post(
        ConsignmentReceivableHeader $receivable
    ): ConsignmentReceivableHeader {
        return DB::transaction(
            function () use ($receivable) {

                $receivable =
                    ConsignmentReceivableHeader::query()
                        ->with('details')
                        ->lockForUpdate()
                        ->findOrFail(
                            $receivable->id
                        );

                if (
                    $receivable->status
                    !== 'Approved'
                ) {
                    throw new RuntimeException(
                        'Only Approved Consignment Receivable can be posted.'
                    );
                }

                if (
                    $receivable->details->isEmpty()
                ) {
                    throw new RuntimeException(
                        'Consignment Receivable must contain at least one settlement.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Payment Account
                |--------------------------------------------------------------------------
                */

                $paymentAccount =
                    ChartOfAccount::query()
                        ->where(
                            'id',
                            $receivable->payment_account_id
                        )
                        ->where(
                            'company_id',
                            $receivable->company_id
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
                | Settlement Receivable Account
                |--------------------------------------------------------------------------
                */

                $settlementReceivableAccount =
                    $this->accountMappingService->getAccount(
                        $receivable->company_id,
                        'settlement_receivable'
                    );

                /*
                |--------------------------------------------------------------------------
                | Fiscal Year
                |--------------------------------------------------------------------------
                */

                $fiscalYear =
                    FiscalYear::query()
                        ->where(
                            'company_id',
                            $receivable->company_id
                        )
                        ->whereDate(
                            'start_date',
                            '<=',
                            $receivable->payment_date
                        )
                        ->whereDate(
                            'end_date',
                            '>=',
                            $receivable->payment_date
                        )
                        ->where(
                            'status',
                            'Open'
                        )
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

                $period =
                    AccountingPeriod::query()
                        ->where(
                            'company_id',
                            $receivable->company_id
                        )
                        ->where(
                            'fiscal_year_id',
                            $fiscalYear->id
                        )
                        ->whereDate(
                            'start_date',
                            '<=',
                            $receivable->payment_date
                        )
                        ->whereDate(
                            'end_date',
                            '>=',
                            $receivable->payment_date
                        )
                        ->where(
                            'status',
                            'Open'
                        )
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
                */

                $journal =
                    AccountingJournal::query()
                        ->where(
                            'company_id',
                            $receivable->company_id
                        )
                        ->where(
                            'code',
                            'ADJ'
                        )
                        ->where(
                            'is_active',
                            true
                        )
                        ->first();

                if (!$journal) {
                    throw new RuntimeException(
                        'Active Adjustment accounting journal not found.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Settlement Balances
                |--------------------------------------------------------------------------
                */

                $creditTotal = 0;

                foreach (
                    $receivable->details
                    as $detail
                ) {

                    $settlement =
                        ConsignmentSettlementHeader::query()
                            ->lockForUpdate()
                            ->find(
                                $detail->settlement_header_id
                            );

                    if (!$settlement) {
                        throw new RuntimeException(
                            'Settlement not found.'
                        );
                    }

                    if (
                        $settlement->status
                        !== 'Posted'
                    ) {
                        throw new RuntimeException(
                            "Settlement {$settlement->settlement_number} "
                            . "is not available for payment."
                        );
                    }

                    if (
                        (int) $settlement->company_id
                        !== (int) $receivable->company_id
                    ) {
                        throw new RuntimeException(
                            'Settlement company does not match receivable.'
                        );
                    }

                    if (
                        (int) $settlement->branch_id
                        !== (int) $receivable->branch_id
                    ) {
                        throw new RuntimeException(
                            'Settlement branch does not match receivable.'
                        );
                    }

                    if (
                        (int) $settlement->reseller_id
                        !== (int) $receivable->reseller_id
                    ) {
                        throw new RuntimeException(
                            'Settlement reseller does not match receivable.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Recalculate Outstanding
                    |--------------------------------------------------------------------------
                    */

                    $previousPaidAmount =
                        (float) ConsignmentReceivableHeader::query()
                            ->where(
                                'reseller_id',
                                $receivable->reseller_id
                            )
                            ->where(
                                'status',
                                'Posted'
                            )
                            ->whereHas(
                                'details',
                                function ($query) use (
                                    $settlement
                                ) {
                                    $query->where(
                                        'settlement_header_id',
                                        $settlement->id
                                    );
                                }
                            )
                            ->withSum(
                                [
                                    'details as settlement_payment_total'
                                    => function ($query) use (
                                        $settlement
                                    ) {
                                        $query->where(
                                            'settlement_header_id',
                                            $settlement->id
                                        );
                                    },
                                ],
                                'payment_amount'
                            )
                            ->get()
                            ->sum(
                                'settlement_payment_total'
                            );

                    $previousPaidAmount =
                        round(
                            $previousPaidAmount,
                            2
                        );

                    $settlementAmount =
                        round(
                            (float) $settlement->receivable_amount,
                            2
                        );

                    $outstanding =
                        round(
                            $settlementAmount
                            - $previousPaidAmount,
                            2
                        );

                    $paymentAmount =
                        round(
                            (float) $detail->payment_amount,
                            2
                        );

                    if ($outstanding <= 0) {
                        throw new RuntimeException(
                            "Settlement {$settlement->settlement_number} "
                            . "has no outstanding receivable balance."
                        );
                    }

                    if (
                        $paymentAmount <= 0
                    ) {
                        throw new RuntimeException(
                            "Payment amount for settlement "
                            . "{$settlement->settlement_number} "
                            . "must be greater than zero."
                        );
                    }

                    if (
                        $paymentAmount
                        > $outstanding
                    ) {
                        throw new RuntimeException(
                            "Payment amount for settlement "
                            . "{$settlement->settlement_number} "
                            . "exceeds outstanding balance."
                        );
                    }

                    $creditTotal +=
                        $paymentAmount;
                }

                $creditTotal =
                    round(
                        $creditTotal,
                        2
                    );

                if ($creditTotal <= 0) {
                    throw new RuntimeException(
                        'Consignment Receivable total must be greater than zero.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Header Total
                |--------------------------------------------------------------------------
                */

                $headerTotal =
                    round(
                        (float) $receivable->total_amount,
                        2
                    );

                if (
                    abs(
                        $creditTotal
                        - $headerTotal
                    ) > 0.01
                ) {
                    throw new RuntimeException(
                        'Consignment Receivable total does not match detail total.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Journal Entry
                |--------------------------------------------------------------------------
                */

                $journalEntry =
                    $this->journalEntryService->create([

                        'branch_id' =>
                            $receivable->branch_id,

                        'accounting_journal_id' =>
                            $journal->id,

                        'fiscal_year_id' =>
                            $fiscalYear->id,

                        'accounting_period_id' =>
                            $period->id,

                        'entry_date' =>
                            $receivable->payment_date,

                        'reference' =>
                            $receivable->number,

                        'description' =>
                            'Consignment Receivable '
                            . $receivable->number,

                        'lines' => [

                            [
                                'account_id' =>
                                    $paymentAccount->id,

                                'description' =>
                                    'Payment - '
                                    . $receivable->number,

                                'debit' =>
                                    $creditTotal,

                                'credit' =>
                                    0,
                            ],

                            [
                                'account_id' =>
                                    $settlementReceivableAccount->id,

                                'description' =>
                                    'Settlement Receivable - '
                                    . $receivable->number,

                                'debit' =>
                                    0,

                                'credit' =>
                                    $creditTotal,
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
                | Mark Posted
                |--------------------------------------------------------------------------
                */

                $receivable->update([

                    'status' =>
                        'Posted',

                    'posted_at' =>
                        now(),

                    'posted_by' =>
                        auth()->id(),

                    'updated_by' =>
                        auth()->id(),
                ]);

                $this->documentActivityService->record(
                    $receivable,
                    'POSTED',
                    'Approved',
                    'Posted',
                    'Consignment Receivable posted.'
                );

                return $receivable->fresh(
                    'details'
                );
            }
        );
    }

    /**
     * Cancel Consignment Receivable.
     */
    public function cancel(
        ConsignmentReceivableHeader $receivable,
        ?string $reason = null
    ): ConsignmentReceivableHeader {
        return DB::transaction(
            function () use (
                $receivable,
                $reason
            ) {

                $receivable =
                    ConsignmentReceivableHeader::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $receivable->id
                        );

                if (
                    in_array(
                        $receivable->status,
                        [
                            'Cancelled',
                        ],
                        true
                    )
                ) {
                    throw new RuntimeException(
                        'Consignment Receivable is already cancelled.'
                    );
                }

                if (
                    $receivable->status
                    === 'Posted'
                ) {
                    throw new RuntimeException(
                        'Posted Consignment Receivable cannot be cancelled.'
                    );
                }

                $reason =
                    trim(
                        (string) $reason
                    );

                if ($reason === '') {
                    throw new RuntimeException(
                        'Cancellation reason is required.'
                    );
                }

                $oldStatus =
                    $receivable->status;

                $receivable->update([

                    'status' =>
                        'Cancelled',

                    'cancelled_at' =>
                        now(),

                    'cancelled_by' =>
                        auth()->id(),

                    'cancel_reason' =>
                        $reason,

                    'updated_by' =>
                        auth()->id(),
                ]);

                $this->documentActivityService->record(
                    $receivable,
                    'CANCELLED',
                    $oldStatus,
                    'Cancelled',
                    'Consignment Receivable cancelled.'
                );

                return $receivable->fresh(
                    'details'
                );
            }
        );
    }

    /**
 * Update Consignment Receivable.
 */
public function update(
    ConsignmentReceivableHeader $receivable,
    array $data
): ConsignmentReceivableHeader {
    return DB::transaction(
        function () use (
            $receivable,
            $data
        ) {

            $receivable =
                ConsignmentReceivableHeader::query()
                    ->with('details')
                    ->lockForUpdate()
                    ->findOrFail(
                        $receivable->id
                    );

            if (
                !in_array(
                    $receivable->status,
                    [
                        'Draft',
                        'Rejected',
                    ],
                    true
                )
            ) {
                throw new RuntimeException(
                    'Only Draft or Rejected Consignment Receivable can be updated.'
                );
            }

            $details =
                $data['details'] ?? [];

            if (empty($details)) {
                throw new RuntimeException(
                    'Consignment Receivable must contain at least one settlement.'
                );
            }

            $paymentDate =
                $data['payment_date']
                ?? null;

            if (!$paymentDate) {
                throw new RuntimeException(
                    'Payment date is required.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Load Settlements
            |--------------------------------------------------------------------------
            */

            $settlementIds =
                collect($details)
                    ->pluck(
                        'settlement_header_id'
                    )
                    ->filter()
                    ->unique()
                    ->values();

            if ($settlementIds->isEmpty()) {
                throw new RuntimeException(
                    'No settlement selected.'
                );
            }

            $settlements =
                ConsignmentSettlementHeader::query()
                    ->whereIn(
                        'id',
                        $settlementIds
                    )
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

            if (
                $settlements->count()
                !== $settlementIds->count()
            ) {
                throw new RuntimeException(
                    'One or more settlements were not found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Company / Branch / Reseller
            |--------------------------------------------------------------------------
            */

            $companyId = null;
            $branchId = null;
            $resellerId = null;

            foreach ($settlements as $settlement) {

                if (
                    $settlement->status
                    !== 'Posted'
                ) {
                    throw new RuntimeException(
                        "Settlement {$settlement->settlement_number} "
                        . "is not available for payment."
                    );
                }

                if (
                    $companyId === null
                ) {
                    $companyId =
                        $settlement->company_id;
                } elseif (
                    (int) $companyId
                    !== (int) $settlement->company_id
                ) {
                    throw new RuntimeException(
                        'All settlements must belong to the same company.'
                    );
                }

                if (
                    $branchId === null
                ) {
                    $branchId =
                        $settlement->branch_id;
                } elseif (
                    (int) $branchId
                    !== (int) $settlement->branch_id
                ) {
                    throw new RuntimeException(
                        'All settlements must belong to the same branch.'
                    );
                }

                if (
                    $resellerId === null
                ) {
                    $resellerId =
                        $settlement->reseller_id;
                } elseif (
                    (int) $resellerId
                    !== (int) $settlement->reseller_id
                ) {
                    throw new RuntimeException(
                        'All settlements must belong to the same reseller.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Header
            |--------------------------------------------------------------------------
            */

            if (
                isset($data['branch_id'])
                && (int) $data['branch_id']
                !== (int) $branchId
            ) {
                throw new RuntimeException(
                    'Selected branch does not match the settlements.'
                );
            }

            if (
                isset($data['reseller_id'])
                && (int) $data['reseller_id']
                !== (int) $resellerId
            ) {
                throw new RuntimeException(
                    'Selected reseller does not match the settlements.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Payment Account
            |--------------------------------------------------------------------------
            */

            $paymentAccount =
                ChartOfAccount::query()
                    ->where(
                        'id',
                        $data['payment_account_id']
                        ?? 0
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

            $receivableDetails = [];

            $totalAmount = 0;

            foreach ($details as $detail) {

                $settlementId =
                    $detail[
                        'settlement_header_id'
                    ] ?? null;

                if (
                    !$settlementId
                    || !$settlements->has(
                        $settlementId
                    )
                ) {
                    throw new RuntimeException(
                        'Invalid settlement selected.'
                    );
                }

                $settlement =
                    $settlements->get(
                        $settlementId
                    );

                /*
                |--------------------------------------------------------------------------
                | Existing Posted Payments
                |--------------------------------------------------------------------------
                */

                $previousPaidAmount =
                    (float) ConsignmentReceivableHeader::query()
                        ->where(
                            'reseller_id',
                            $resellerId
                        )
                        ->where(
                            'status',
                            'Posted'
                        )
                        ->whereHas(
                            'details',
                            function ($query) use (
                                $settlementId
                            ) {
                                $query->where(
                                    'settlement_header_id',
                                    $settlementId
                                );
                            }
                        )
                        ->withSum(
                            [
                                'details as settlement_payment_total'
                                => function ($query) use (
                                    $settlementId
                                ) {
                                    $query->where(
                                        'settlement_header_id',
                                        $settlementId
                                    );
                                },
                            ],
                            'payment_amount'
                        )
                        ->get()
                        ->sum(
                            'settlement_payment_total'
                        );

                $previousPaidAmount =
                    round(
                        $previousPaidAmount,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | Actual Receivable
                |--------------------------------------------------------------------------
                */

                $settlementReceivable =
                    round(
                        (float) $settlement->receivable_amount,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | Settlement Amount Snapshot
                |--------------------------------------------------------------------------
                |
                | Settlement Amount = Grand Total.
                |
                */

                $settlementAmount =
                    round(
                        (float) $settlement->grand_total,
                        2
                    );

                /*
                |--------------------------------------------------------------------------
                | Previous Outstanding
                |--------------------------------------------------------------------------
                |
                | Outstanding tetap menggunakan receivable_amount.
                |
                */

                $previousOutstandingAmount =
                    round(
                        $settlementReceivable
                        - $previousPaidAmount,
                        2
                    );

                if (
                    $previousOutstandingAmount
                    <= 0
                ) {
                    throw new RuntimeException(
                        "Settlement {$settlement->settlement_number} "
                        . "has no outstanding receivable balance."
                    );
                }

                $paymentAmount =
                    round(
                        (float) (
                            $detail[
                                'payment_amount'
                            ] ?? 0
                        ),
                        2
                    );

                if (
                    $paymentAmount <= 0
                ) {
                    throw new RuntimeException(
                        "Payment amount for settlement "
                        . "{$settlement->settlement_number} "
                        . "must be greater than zero."
                    );
                }

                if (
                    $paymentAmount
                    > $previousOutstandingAmount
                ) {
                    throw new RuntimeException(
                        "Payment amount for settlement "
                        . "{$settlement->settlement_number} "
                        . "cannot exceed outstanding amount "
                        . number_format(
                            $previousOutstandingAmount,
                            2,
                            '.',
                            ','
                        ) . '.'
                    );
                }

                $receivableDetails[] = [

                    'settlement_header_id' =>
                        $settlement->id,

                    /*
                    |--------------------------------------------------------------------------
                    | IMPORTANT
                    |--------------------------------------------------------------------------
                    | Display Settlement Amount = Grand Total
                    |--------------------------------------------------------------------------
                    */

                    'settlement_amount' =>
                        $settlementAmount,

                    'previous_paid_amount' =>
                        $previousPaidAmount,

                    'previous_outstanding_amount' =>
                        $previousOutstandingAmount,

                    'payment_amount' =>
                        $paymentAmount,

                    'remarks' =>
                        $detail['remarks']
                        ?? null,
                ];

                $totalAmount +=
                    $paymentAmount;
            }

            $totalAmount =
                round(
                    $totalAmount,
                    2
                );

            if (
                $totalAmount <= 0
            ) {
                throw new RuntimeException(
                    'Consignment Receivable total must be greater than zero.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update Header
            |--------------------------------------------------------------------------
            */

            $receivable->update([

                'company_id' =>
                    $companyId,

                'branch_id' =>
                    $branchId,

                'payment_date' =>
                    $paymentDate,

                'reseller_id' =>
                    $resellerId,

                'payment_method' =>
                    $data['payment_method']
                    ?? null,

                'payment_account_id' =>
                    $paymentAccount->id,

                'total_amount' =>
                    $totalAmount,

                'remarks' =>
                    $data['remarks']
                    ?? null,

                'updated_by' =>
                    auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Replace Details
            |--------------------------------------------------------------------------
            */

            $receivable
                ->details()
                ->delete();

            foreach (
                $receivableDetails
                as $detail
            ) {
                $receivable
                    ->details()
                    ->create($detail);
            }

            /*
            |--------------------------------------------------------------------------
            | Activity
            |--------------------------------------------------------------------------
            */

            $this->documentActivityService->record(
                $receivable,
                'UPDATED',
                $receivable->getOriginal(
                    'status'
                ),
                $receivable->status,
                'Consignment Receivable updated.'
            );

            return $receivable->fresh(
                'details'
            );
        }
    );
}

    /**
     * Reject Consignment Receivable.
     */
    public function reject(
        ConsignmentReceivableHeader $receivable,
        string $reason
    ): ConsignmentReceivableHeader {
        return DB::transaction(
            function () use (
                $receivable,
                $reason
            ) {

                $receivable->refresh();

                if (
                    $receivable->status
                    !== 'Submitted'
                ) {
                    throw new RuntimeException(
                        'Only Submitted Consignment Receivable can be rejected.'
                    );
                }

                $reason =
                    trim($reason);

                if ($reason === '') {
                    throw new RuntimeException(
                        'Rejection reason is required.'
                    );
                }

                $receivable->update([

                    'status' =>
                        'Rejected',

                    'rejected_at' =>
                        now(),

                    'rejected_by' =>
                        auth()->id(),

                    'reject_reason' =>
                        $reason,

                    'updated_by' =>
                        auth()->id(),
                ]);

                $this->documentActivityService->record(
                    $receivable,
                    'REJECTED',
                    'Submitted',
                    'Rejected',
                    'Consignment Receivable rejected.'
                );

                return $receivable->fresh(
                    'details'
                );
            }
        );
    }
}