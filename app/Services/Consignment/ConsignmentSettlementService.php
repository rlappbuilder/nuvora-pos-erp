<?php

namespace App\Services\Consignment;

use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Models\Inventory\ProductStock;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\Product\ProductVariant;
use App\Models\Reseller\Reseller;
use App\Models\Reseller\ResellerPriceHistory;
use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;
use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementDetail;
use App\Services\Accounting\AccountMappingService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;

class ConsignmentSettlementService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected DocumentActivityService $documentActivityService;

    protected AccountMappingService $accountMappingService;

    protected JournalEntryService $journalEntryService;

    protected InventoryService $inventoryService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService,
        DocumentActivityService $documentActivityService,
        AccountMappingService $accountMappingService,
        JournalEntryService $journalEntryService,
        InventoryService $inventoryService
    ) {
        $this->codeGeneratorService =
            $codeGeneratorService;

        $this->documentActivityService =
            $documentActivityService;

        $this->accountMappingService =
            $accountMappingService;

        $this->journalEntryService =
            $journalEntryService;

        $this->inventoryService =
            $inventoryService;
    }


    /*
    |--------------------------------------------------------------------------
    | Create Settlement - POSTED
    |--------------------------------------------------------------------------
    */

    public function createSettlement(
        array $data
    ): ConsignmentSettlementHeader {

        return DB::transaction(
            function () use ($data) {

                /*
                |--------------------------------------------------------------------------
                | Validate Details
                |--------------------------------------------------------------------------
                */

                if (
                    empty($data['details']) ||
                    ! is_array($data['details'])
                ) {

                    throw new \RuntimeException(
                        'Settlement must have at least one detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Resolve Branch
                |--------------------------------------------------------------------------
                */

                $branch =
                    Branch::query()
                        ->findOrFail(
                            $data['branch_id']
                        );


                /*
                |--------------------------------------------------------------------------
                | Resolve Company
                |--------------------------------------------------------------------------
                */

                $companyId =
                    $branch->company_id;


                /*
                |--------------------------------------------------------------------------
                | Validate Warehouse
                |--------------------------------------------------------------------------
                */

                $warehouse =
                    Warehouse::query()
                        ->where(
                            'id',
                            $data['warehouse_id']
                        )
                        ->where(
                            'branch_id',
                            $branch->id
                        )
                        ->first();

                if (! $warehouse) {

                    throw new \RuntimeException(
                        'Selected warehouse is invalid for this branch.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Reseller
                |--------------------------------------------------------------------------
                */

                $reseller =
                    Reseller::query()
                        ->where(
                            'id',
                            $data['reseller_id']
                        )
                        ->where(
                            'company_id',
                            $companyId
                        )
                        ->where(
                            'status',
                            true
                        )
                        ->first();

                if (! $reseller) {

                    throw new \RuntimeException(
                        'Selected reseller is invalid for this company.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Prepare Settlement Details
                |--------------------------------------------------------------------------
                */

                $preparedDetails = [];

                $subtotal = 0;

                $totalCogs = 0;

                $detailKeys = [];


                foreach (
                    $data['details']
                    as $detail
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Prevent Duplicate Product + Unit
                    |--------------------------------------------------------------------------
                    */

                    $detailKey =
                        $detail['product_variant_id'] .
                        '-' .
                        $detail['unit_id'];

                    if (
                        isset(
                            $detailKeys[$detailKey]
                        )
                    ) {

                        throw new \RuntimeException(
                            'Duplicate product variant and unit is not allowed in settlement.'
                        );

                    }

                    $detailKeys[$detailKey] = true;


                    /*
                    |--------------------------------------------------------------------------
                    | Product Variant
                    |--------------------------------------------------------------------------
                    */

                    $variant =
                        ProductVariant::query()
                            ->with('product')
                            ->findOrFail(
                                $detail['product_variant_id']
                            );

                /*
                |--------------------------------------------------------------------------
                | Validate Unit Belongs To Variant
                |--------------------------------------------------------------------------
                */

                $unitExists =
                    $variant
                        ->units()
                        ->where(
                            'unit_id',
                            $detail['unit_id']
                        )
                        ->exists();

                if (! $unitExists) {

                    throw new \RuntimeException(
                        'Selected unit is invalid for product variant ' .
                        $variant->id .
                        '.'
                    );

                }


                    /*
                    |--------------------------------------------------------------------------
                    | Quantity Sold
                    |--------------------------------------------------------------------------
                    */

                    $qtySold =
                        (float) (
                            $detail['qty_sold']
                            ?? 0
                        );


                    if (
                        $qtySold <= 0
                    ) {

                        throw new \RuntimeException(
                            'Quantity sold must be greater than zero.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Consignment Stock
                    |--------------------------------------------------------------------------
                    */

                    $stock =
                        ProductStock::query()
                            ->where(
                                'company_id',
                                $companyId
                            )
                            ->where(
                                'branch_id',
                                $branch->id
                            )
                            ->where(
                                'warehouse_id',
                                $warehouse->id
                            )
                            ->where(
                                'product_variant_id',
                                $variant->id
                            )
                            ->where(
                                'unit_id',
                                $detail['unit_id']
                            )
                            ->where(
                                'reseller_id',
                                $reseller->id
                            )
                            ->lockForUpdate()
                            ->first();


                    if (! $stock) {

                        throw new \RuntimeException(
                            'Consignment stock was not found for product variant ' .
                            $variant->id .
                            '.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Available Stock
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (float) $stock->available_qty <
                        $qtySold
                    ) {

                        throw new \RuntimeException(
                            'Insufficient consignment stock for product variant ' .
                            $variant->id .
                            '. Available: ' .
                            $stock->available_qty .
                            '.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | HPP
                    |--------------------------------------------------------------------------
                    */

                    $unitCost =
                        (float) $stock->average_cost;


                    $totalCost =
                        $qtySold *
                        $unitCost;


                    /*
                    |--------------------------------------------------------------------------
                    | Resolve Price History
                    |--------------------------------------------------------------------------
                    */

                    $priceHistory =
                        ResellerPriceHistory::query()
                            ->where(
                                'reseller_id',
                                $reseller->id
                            )
                            ->where(
                                'product_id',
                                $variant->product_id
                            )
                            ->whereDate(
                                'effective_from',
                                '<=',
                                $data['settlement_date']
                            )
                            ->where(function ($query) use ($data) {

                                $query
                                    ->whereNull(
                                        'effective_to'
                                    )
                                    ->orWhereDate(
                                        'effective_to',
                                        '>=',
                                        $data['settlement_date']
                                    );

                            })
                            ->orderByDesc(
                                'effective_from'
                            )
                            ->first();


                    /*
                    |--------------------------------------------------------------------------
                    | Fallback To Current Price
                    |--------------------------------------------------------------------------
                    |
                    | Existing reseller price is used when no history
                    | record is available.
                    |--------------------------------------------------------------------------
                    */

                    if (! $priceHistory) {

                        $currentPrice =
                            \App\Models\Reseller\ResellerPrice::query()
                                ->where(
                                    'reseller_id',
                                    $reseller->id
                                )
                                ->where(
                                    'product_id',
                                    $variant->product_id
                                )
                                ->first();

                        if (! $currentPrice) {

                            throw new \RuntimeException(
                                'Consignment price was not found for product ' .
                                $variant->product_id .
                                '.'
                            );

                        }

                        $unitPrice =
                            (float) $currentPrice->price;

                    } else {

                        $unitPrice =
                            (float) $priceHistory->price;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Price
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $unitPrice < 0
                    ) {

                        throw new \RuntimeException(
                            'Consignment price cannot be negative.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Sales Amount
                    |--------------------------------------------------------------------------
                    */

                    $totalAmount =
                        $qtySold *
                        $unitPrice;


                    $subtotal +=
                        $totalAmount;

                    $totalCogs +=
                        $totalCost;


                    /*
                    |--------------------------------------------------------------------------
                    | Prepare Detail
                    |--------------------------------------------------------------------------
                    */

                    $preparedDetails[] = [

                        'product_variant_id' =>
                            $variant->id,

                        'unit_id' =>
                            $detail['unit_id'],

                        'qty_sold' =>
                            $qtySold,

                        'unit_price' =>
                            round(
                                $unitPrice,
                                2
                            ),

                        'total_amount' =>
                            round(
                                $totalAmount,
                                2
                            ),

                        'unit_cost' =>
                            round(
                                $unitCost,
                                2
                            ),

                        'total_cost' =>
                            round(
                                $totalCost,
                                2
                            ),

                    ];

                }


                /*
                |--------------------------------------------------------------------------
                | Adjustment
                |--------------------------------------------------------------------------
                */

                $adjustmentAmount = 0;

                /*
                |--------------------------------------------------------------------------
                | Payment
                |--------------------------------------------------------------------------
                */

                $paymentAmount =
                    (float) (
                        $data['payment_amount']
                        ?? 0
                    );
               /*
                |--------------------------------------------------------------------------
                | Grand Total
                |--------------------------------------------------------------------------
                */

                $grandTotal =
                    $subtotal +
                    $adjustmentAmount;


                if (
                    $grandTotal < 0
                ) {

                    throw new \RuntimeException(
                        'Settlement grand total cannot be negative.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Payment
                |--------------------------------------------------------------------------
                */

                if (
                    $paymentAmount < 0
                ) {

                    throw new \RuntimeException(
                        'Payment amount cannot be negative.'
                    );

                }


                if (
                    $paymentAmount > $grandTotal
                ) {

                    throw new \RuntimeException(
                        'Payment amount cannot be greater than settlement grand total.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Receivable
                |--------------------------------------------------------------------------
                */

                $receivableAmount =
                    $grandTotal -
                    $paymentAmount;


                /*
                |--------------------------------------------------------------------------
                | Payment Status
                |--------------------------------------------------------------------------
                */

                $paymentStatus =
                    $paymentAmount >= $grandTotal
                        ? 'Paid'
                        : 'Receivable';
                /*
                |--------------------------------------------------------------------------
                | Resolve Fiscal Year
                |--------------------------------------------------------------------------
                */

                $fiscalYear =
                    FiscalYear::query()
                        ->where(
                            'company_id',
                            $companyId
                        )
                        ->whereDate(
                            'start_date',
                            '<=',
                            $data['settlement_date']
                        )
                        ->whereDate(
                            'end_date',
                            '>=',
                            $data['settlement_date']
                        )
                        ->where(
                            'status',
                            'Open'
                        )
                        ->first();


                if (! $fiscalYear) {

                    throw new \RuntimeException(
                        'No open fiscal year found for settlement date.'
                    );

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
                            $companyId
                        )
                        ->where(
                            'fiscal_year_id',
                            $fiscalYear->id
                        )
                        ->whereDate(
                            'start_date',
                            '<=',
                            $data['settlement_date']
                        )
                        ->whereDate(
                            'end_date',
                            '>=',
                            $data['settlement_date']
                        )
                        ->where(
                            'status',
                            'Open'
                        )
                        ->first();


                if (! $accountingPeriod) {

                    throw new \RuntimeException(
                        'No open accounting period found for settlement date.'
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
                            $companyId
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


                if (! $journal) {

                    throw new \RuntimeException(
                        'Adjustment accounting journal is not configured.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Account Mapping
                |--------------------------------------------------------------------------
                */

                $receivableAccount =
                    $this
                        ->accountMappingService
                        ->getAccount(
                            $companyId,
                            'settlement_receivable'
                        );
                /*
                |--------------------------------------------------------------------------
                | Consignment Cash Account
                |--------------------------------------------------------------------------
                */

                $consignmentCashAccount =
                    $this
                        ->accountMappingService
                        ->getAccount(
                            $companyId,
                            'consignment_cash'
                        );

                $revenueAccount =
                    $this
                        ->accountMappingService
                        ->getAccount(
                            $companyId,
                            'settlement_revenue'
                        );


                $cogsAccount =
                    $this
                        ->accountMappingService
                        ->getAccount(
                            $companyId,
                            'settlement_cogs'
                        );


                $consignmentInventoryAccount =
                    $this
                        ->accountMappingService
                        ->getAccount(
                            $companyId,
                            'inventory_consignment'
                        );


                /*
                |--------------------------------------------------------------------------
                | Journal Lines
                |--------------------------------------------------------------------------
                */

                $lines = [];

                /*
                |--------------------------------------------------------------------------
                | Sales Journal
                |--------------------------------------------------------------------------
                | Payment portion:
                | Dr Consignment Cash
                |
                | Receivable portion:
                | Dr Trade Receivable
                |
                | Total:
                | Cr Merchandise Sales
                |--------------------------------------------------------------------------
                */

                if (
                    $grandTotal > 0
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Dr Consignment Cash
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $paymentAmount > 0
                    ) {

                        $lines[] = [

                            'account_id' =>
                                $consignmentCashAccount->id,

                            'debit' =>
                                round(
                                    $paymentAmount,
                                    2
                                ),

                            'credit' =>
                                0,

                            'description' =>
                                'Consignment settlement payment.',

                        ];

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Dr Trade Receivable
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $receivableAmount > 0
                    ) {

                        $lines[] = [

                            'account_id' =>
                                $receivableAccount->id,

                            'debit' =>
                                round(
                                    $receivableAmount,
                                    2
                                ),

                            'credit' =>
                                0,

                            'description' =>
                                'Consignment settlement receivable.',

                        ];

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Cr Merchandise Sales
                    |--------------------------------------------------------------------------
                    */

                    $lines[] = [

                        'account_id' =>
                            $revenueAccount->id,

                        'debit' =>
                            0,

                        'credit' =>
                            round(
                                $grandTotal,
                                2
                            ),

                        'description' =>
                            'Consignment settlement sales revenue.',

                    ];

                }

                /*
                |--------------------------------------------------------------------------
                | Dr COGS
                | Cr Inventory - Consignment
                |--------------------------------------------------------------------------
                */

                if (
                    $totalCogs > 0
                ) {

                    $lines[] = [

                        'account_id' =>
                            $cogsAccount->id,

                        'debit' =>
                            round(
                                $totalCogs,
                                2
                            ),

                        'credit' =>
                            0,

                        'description' =>
                            'COGS for consignment settlement.',

                    ];


                    $lines[] = [

                        'account_id' =>
                            $consignmentInventoryAccount->id,

                        'debit' =>
                            0,

                        'credit' =>
                            round(
                                $totalCogs,
                                2
                            ),

                        'description' =>
                            'Consignment inventory sold.',

                    ];

                }


                /*
                |--------------------------------------------------------------------------
                | Create Settlement Header
                |--------------------------------------------------------------------------
                */

                $header =
                    ConsignmentSettlementHeader::create([

                        'company_id' =>
                            $companyId,

                        'branch_id' =>
                            $branch->id,

                        'warehouse_id' =>
                            $warehouse->id,

                        'reseller_id' =>
                            $reseller->id,

                        'settlement_number' =>
                            $this
                                ->codeGeneratorService
                                ->next(
                                    'consignment_settlement'
                                ),

                        'settlement_date' =>
                            $data['settlement_date'],

                        'period_from' =>
                            $data['period_from'],

                        'period_to' =>
                            $data['period_to'],

                        'status' =>
                            'Posted',

                        'subtotal' =>
                            round(
                                $subtotal,
                                2
                            ),

                        'adjustment_amount' =>
                            0,

                      'grand_total' =>
                            round(
                                $grandTotal,
                                2
                            ),

                        'payment_amount' =>
                            round(
                                $paymentAmount,
                                2
                            ),

                        'receivable_amount' =>
                            round(
                                $receivableAmount,
                                2
                            ),

                        'payment_status' =>
                            $paymentStatus,

                        'remarks' =>
                            $data['remarks'] ?? null,

                        'created_by' =>
                            auth()->id(),

                        'posted_by' =>
                            auth()->id(),

                        'posted_at' =>
                            now(),

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Create Settlement Details
                |--------------------------------------------------------------------------
                */

                foreach (
                    $preparedDetails
                    as $detail
                ) {

                    ConsignmentSettlementDetail::create([

                        'settlement_header_id' =>
                            $header->id,

                        'product_variant_id' =>
                            $detail['product_variant_id'],

                        'unit_id' =>
                            $detail['unit_id'],

                        'qty_sold' =>
                            $detail['qty_sold'],

                        'unit_price' =>
                            $detail['unit_price'],

                        'total_amount' =>
                            $detail['total_amount'],

                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Create Journal
                |--------------------------------------------------------------------------
                */

                $journalEntry =
                    $this
                        ->journalEntryService
                        ->create([

                            'company_id' =>
                                $companyId,

                            'branch_id' =>
                                $branch->id,

                            'accounting_journal_id' =>
                                $journal->id,

                            'fiscal_year_id' =>
                                $fiscalYear->id,

                            'accounting_period_id' =>
                                $accountingPeriod->id,

                            'entry_date' =>
                                $data['settlement_date'],

                            'reference_type' =>
                                'CONSIGNMENT_SETTLEMENT',

                            'reference_id' =>
                                $header->id,

                            'reference_number' =>
                                $header->settlement_number,

                            'description' =>
                                'Consignment settlement ' .
                                $header->settlement_number,

                            'lines' =>
                                $lines,

                            'created_by' =>
                                auth()->id(),

                        ]);


                /*
                |--------------------------------------------------------------------------
                | Post Journal
                |--------------------------------------------------------------------------
                */

                $this
                    ->journalEntryService
                    ->post(
                        $journalEntry
                    );


                /*
                |--------------------------------------------------------------------------
                | Inventory OUT
                |--------------------------------------------------------------------------
                */

                foreach (
                    $preparedDetails
                    as $detail
                ) {

                    $this
                        ->inventoryService
                        ->stockOut([

                            'company_id' =>
                                $companyId,

                            'branch_id' =>
                                $branch->id,

                            'warehouse_id' =>
                                $warehouse->id,

                            'product_variant_id' =>
                                $detail['product_variant_id'],

                            'unit_id' =>
                                $detail['unit_id'],

                            'qty' =>
                                $detail['qty_sold'],

                            'unit_cost' =>
                                $detail['unit_cost'],

                            'total_cost' =>
                                $detail['total_cost'],

                            'transaction_date' =>
                                $data['settlement_date'],

                            'reference_type' =>
                                'SETTLEMENT',

                            'reference_id' =>
                                $header->id,

                            'reference_number' =>
                                $header->settlement_number,

                            'description' =>
                                'Consignment stock sold by reseller.',

                            'created_by' =>
                                auth()->id(),

                            'reseller_id' =>
                                $reseller->id,

                        ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Document Activity
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $header,

                        'POSTED',

                        null,

                        'Posted',

                        'Consignment settlement posted.'

                    );


                return $header;

            }
        );
    }


  /*
|--------------------------------------------------------------------------
| Cancel Settlement
|--------------------------------------------------------------------------
*/

public function cancelSettlement(
    ConsignmentSettlementHeader $settlement,
    string $reason
): void {

    DB::transaction(
        function () use (
            $settlement,
            $reason
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $settlement =
                ConsignmentSettlementHeader::query()
                    ->with('details')
                    ->lockForUpdate()
                    ->findOrFail(
                        $settlement->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $settlement->status !== 'Posted'
            ) {

                throw new \RuntimeException(
                    'Only Posted settlement can be cancelled.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Reason
            |--------------------------------------------------------------------------
            */

            if (
                trim($reason) === ''
            ) {

                throw new \InvalidArgumentException(
                    'Cancellation reason is required.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Resolve Fiscal Year
            |--------------------------------------------------------------------------
            */

            $fiscalYear =
                FiscalYear::query()
                    ->where(
                        'company_id',
                        $settlement->company_id
                    )
                    ->whereDate(
                        'start_date',
                        '<=',
                        now()
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        now()
                    )
                    ->where(
                        'status',
                        'Open'
                    )
                    ->first();


            if (! $fiscalYear) {

                throw new \RuntimeException(
                    'No open fiscal year found for settlement cancellation.'
                );

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
                        $settlement->company_id
                    )
                    ->where(
                        'fiscal_year_id',
                        $fiscalYear->id
                    )
                    ->whereDate(
                        'start_date',
                        '<=',
                        now()
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        now()
                    )
                    ->where(
                        'status',
                        'Open'
                    )
                    ->first();


            if (! $accountingPeriod) {

                throw new \RuntimeException(
                    'No open accounting period found for settlement cancellation.'
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
                        $settlement->company_id
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


            if (! $journal) {

                throw new \RuntimeException(
                    'Adjustment accounting journal is not configured.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Account Mapping
            |--------------------------------------------------------------------------
            */

            $receivableAccount =
                $this
                    ->accountMappingService
                    ->getAccount(
                        $settlement->company_id,
                        'settlement_receivable'
                    );


            $consignmentCashAccount =
                $this
                    ->accountMappingService
                    ->getAccount(
                        $settlement->company_id,
                        'consignment_cash'
                    );


            $revenueAccount =
                $this
                    ->accountMappingService
                    ->getAccount(
                        $settlement->company_id,
                        'settlement_revenue'
                    );


            $cogsAccount =
                $this
                    ->accountMappingService
                    ->getAccount(
                        $settlement->company_id,
                        'settlement_cogs'
                    );


            $consignmentInventoryAccount =
                $this
                    ->accountMappingService
                    ->getAccount(
                        $settlement->company_id,
                        'inventory_consignment'
                    );


            /*
            |--------------------------------------------------------------------------
            | Load Original Inventory Movements
            |--------------------------------------------------------------------------
            */

            $movements =
                $settlement
                    ->movements()
                    ->where(
                        'qty_out',
                        '>',
                        0
                    )
                    ->lockForUpdate()
                    ->get();


            if (
                $movements->isEmpty()
            ) {

                throw new \RuntimeException(
                    'Original settlement inventory movement was not found.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Calculate Original COGS
            |--------------------------------------------------------------------------
            */

            $totalCogs = 0;


            foreach (
                $movements
                as $movement
            ) {

                $totalCogs +=
                    (float) $movement->total_cost;

            }


            /*
            |--------------------------------------------------------------------------
            | Reverse Inventory
            |--------------------------------------------------------------------------
            */

            foreach (
                $movements
                as $movement
            ) {

                $this
                    ->inventoryService
                    ->stockIn([

                        'company_id' =>
                            $movement->company_id,

                        'branch_id' =>
                            $movement->branch_id,

                        'warehouse_id' =>
                            $movement->warehouse_id,

                        'product_variant_id' =>
                            $movement->product_variant_id,

                        'unit_id' =>
                            $movement->unit_id,

                        'qty' =>
                            $movement->qty_out,

                        'unit_cost' =>
                            $movement->unit_cost,

                        'total_cost' =>
                            $movement->total_cost,

                        'transaction_date' =>
                            now(),

                        'reference_type' =>
                            'SETTLEMENT_CANCEL',

                        'reference_id' =>
                            $settlement->id,

                        'reference_number' =>
                            $settlement->settlement_number,

                        'description' =>
                            'Settlement cancellation - return to consignment stock.',

                        'created_by' =>
                            auth()->id(),

                        'reseller_id' =>
                            $settlement->reseller_id,

                    ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Payment & Receivable Values
            |--------------------------------------------------------------------------
            */

            $grandTotal =
                (float) $settlement->grand_total;

            $paymentAmount =
                (float) $settlement->payment_amount;

            $receivableAmount =
                (float) $settlement->receivable_amount;


            /*
            |--------------------------------------------------------------------------
            | Revenue Reversal
            |--------------------------------------------------------------------------
            |
            | Original:
            |
            | Dr Consignment Cash
            | Dr Trade Receivable
            |     Cr Merchandise Sales
            |
            | Cancellation:
            |
            | Dr Merchandise Sales
            |     Cr Consignment Cash
            |     Cr Trade Receivable
            |--------------------------------------------------------------------------
            */

            $lines = [];


            /*
            |--------------------------------------------------------------------------
            | Dr Merchandise Sales
            |--------------------------------------------------------------------------
            */

            if (
                $grandTotal > 0
            ) {

                $lines[] = [

                    'account_id' =>
                        $revenueAccount->id,

                    'debit' =>
                        round(
                            $grandTotal,
                            2
                        ),

                    'credit' =>
                        0,

                    'description' =>
                        'Sales reversal for cancelled settlement ' .
                        $settlement->settlement_number,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Cr Consignment Cash
            |--------------------------------------------------------------------------
            */

            if (
                $paymentAmount > 0
            ) {

                $lines[] = [

                    'account_id' =>
                        $consignmentCashAccount->id,

                    'debit' =>
                        0,

                    'credit' =>
                        round(
                            $paymentAmount,
                            2
                        ),

                    'description' =>
                        'Consignment cash reversal for cancelled settlement ' .
                        $settlement->settlement_number,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Cr Trade Receivable
            |--------------------------------------------------------------------------
            */

            if (
                $receivableAmount > 0
            ) {

                $lines[] = [

                    'account_id' =>
                        $receivableAccount->id,

                    'debit' =>
                        0,

                    'credit' =>
                        round(
                            $receivableAmount,
                            2
                        ),

                    'description' =>
                        'Receivable reversal for cancelled settlement ' .
                        $settlement->settlement_number,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | COGS Reversal
            |--------------------------------------------------------------------------
            |
            | Original:
            |
            | Dr Merchandise COGS
            |     Cr Inventory - Consignment
            |
            | Cancellation:
            |
            | Dr Inventory - Consignment
            |     Cr Merchandise COGS
            |--------------------------------------------------------------------------
            */

            if (
                $totalCogs > 0
            ) {

                $lines[] = [

                    'account_id' =>
                        $consignmentInventoryAccount->id,

                    'debit' =>
                        round(
                            $totalCogs,
                            2
                        ),

                    'credit' =>
                        0,

                    'description' =>
                        'Consignment inventory reversal for cancelled settlement ' .
                        $settlement->settlement_number,

                ];


                $lines[] = [

                    'account_id' =>
                        $cogsAccount->id,

                    'debit' =>
                        0,

                    'credit' =>
                        round(
                            $totalCogs,
                            2
                        ),

                    'description' =>
                        'COGS reversal for cancelled settlement ' .
                        $settlement->settlement_number,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Create Reversal Journal
            |--------------------------------------------------------------------------
            */

            $journalEntry =
                $this
                    ->journalEntryService
                    ->create([

                        'company_id' =>
                            $settlement->company_id,

                        'branch_id' =>
                            $settlement->branch_id,

                        'accounting_journal_id' =>
                            $journal->id,

                        'fiscal_year_id' =>
                            $fiscalYear->id,

                        'accounting_period_id' =>
                            $accountingPeriod->id,

                        'entry_date' =>
                            now(),

                        'reference_type' =>
                            'CONSIGNMENT_SETTLEMENT_CANCEL',

                        'reference_id' =>
                            $settlement->id,

                        'reference_number' =>
                            $settlement->settlement_number,

                        'description' =>
                            'Cancellation of settlement ' .
                            $settlement->settlement_number,

                        'lines' =>
                            $lines,

                        'created_by' =>
                            auth()->id(),

                    ]);


            /*
            |--------------------------------------------------------------------------
            | Post Reversal Journal
            |--------------------------------------------------------------------------
            */

            $this
                ->journalEntryService
                ->post(
                    $journalEntry
                );


            /*
            |--------------------------------------------------------------------------
            | Mark Cancelled
            |--------------------------------------------------------------------------
            */

            $oldStatus =
                $settlement->status;


            $settlement->update([

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


            /*
            |--------------------------------------------------------------------------
            | Document Activity
            |--------------------------------------------------------------------------
            */

            $this
                ->documentActivityService
                ->record(

                    $settlement,

                    'CANCELLED',

                    $oldStatus,

                    'Cancelled',

                    'Consignment settlement cancelled.',

                    [

                        'reason' =>
                            $reason,

                    ]

                );

        }
    );
}
}