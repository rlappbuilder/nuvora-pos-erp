<?php

namespace App\Services\Consignment;

use App\Models\Reseller\ConsignmentOut\ConsignmentOut;
use App\Models\Reseller\ConsignmentOut\ConsignmentOutDetail;
use App\Models\Product\ProductVariant;
use App\Models\Inventory\ProductStock;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\AccountingJournal;
use App\Services\Accounting\AccountMappingService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Inventory\InventoryService;
use App\Models\Reseller\ResellerStockPriceLayer;
class ConsignmentOutService
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
    | Create
    |--------------------------------------------------------------------------
    */

    public function createConsignmentOut(
        array $data
    ): ConsignmentOut {

        return DB::transaction(
            function () use ($data) {

                /*
                |--------------------------------------------------------------------------
                | Validate Details
                |--------------------------------------------------------------------------
                */

                if (
                    empty($data['details'])
                ) {

                    throw new \RuntimeException(
                        'Consignment out must have at least one detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Prepare Details
                |--------------------------------------------------------------------------
                */

                $preparedDetails = [];


                foreach (
                    $data['details']
                    as $detail
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Product Variant
                    |--------------------------------------------------------------------------
                    */

                    $productVariant =
                        ProductVariant::query()
                            ->findOrFail(
                                $detail[
                                    'product_variant_id'
                                ]
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | Quantity
                    |--------------------------------------------------------------------------
                    */

                    $qty =
                        (float) (
                            $detail['qty']
                            ?? 0
                        );


                    if (
                        $qty <= 0
                    ) {

                        throw new \RuntimeException(
                            'Quantity must be greater than zero.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Unit Price
                    |--------------------------------------------------------------------------
                    */

                    $unitPrice =
                        (float) (
                            $detail['unit_price']
                            ?? 0
                        );


                    if (
                        $unitPrice < 0
                    ) {

                        throw new \RuntimeException(
                            'Consignment price cannot be negative.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Calculate Total
                    |--------------------------------------------------------------------------
                    */

                    $totalPrice =
                        $qty *
                        $unitPrice;


                    /*
                    |--------------------------------------------------------------------------
                    | Prepare Detail
                    |--------------------------------------------------------------------------
                    */

                    $preparedDetails[] = [

                        'product_variant_id' =>
                            $productVariant->id,

                        'unit_id' =>
                            $detail['unit_id'],

                        'qty' =>
                            $qty,

                        'unit_price' =>
                            $unitPrice,

                        'total_price' =>
                            $totalPrice,

                    ];

                }


                /*
                |--------------------------------------------------------------------------
                | Create Header - DRAFT
                |--------------------------------------------------------------------------
                */

                $header =
                    ConsignmentOut::create([

                        /*
                        |--------------------------------------------------------------------------
                        | Organization
                        |--------------------------------------------------------------------------
                        */

                        'company_id' =>
                            $data[
                                'company_id'
                            ],

                        'branch_id' =>
                            $data[
                                'branch_id'
                            ],

                        'warehouse_id' =>
                            $data[
                                'warehouse_id'
                            ],

                        'reseller_id' =>
                            $data[
                                'reseller_id'
                            ],

                        /*
                        |--------------------------------------------------------------------------
                        | Document
                        |--------------------------------------------------------------------------
                        */

                        'consignment_out_number' =>
                            $this
                                ->codeGeneratorService
                                ->next(
                                    'consignment_out'
                                ),

                        /*
                        |--------------------------------------------------------------------------
                        | Transaction
                        |--------------------------------------------------------------------------
                        */

                        'transaction_date' =>
                            $data[
                                'transaction_date'
                            ],

                        'reference_number' =>
                            ! empty(
                                trim(
                                    $data[
                                        'reference_number'
                                    ] ?? ''
                                )
                            )
                                ? trim(
                                    $data[
                                        'reference_number'
                                    ]
                                )
                                : null,

                        /*
                        |--------------------------------------------------------------------------
                        | Status
                        |--------------------------------------------------------------------------
                        */

                        'status' =>
                            'Draft',

                        /*
                        |--------------------------------------------------------------------------
                        | Notes
                        |--------------------------------------------------------------------------
                        */

                        'remarks' =>
                            $data[
                                'remarks'
                            ] ?? null,

                        /*
                        |--------------------------------------------------------------------------
                        | Audit
                        |--------------------------------------------------------------------------
                        */

                        'created_by' =>
                            auth()->id(),

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Create Details
                |--------------------------------------------------------------------------
                */

                foreach (
                    $preparedDetails
                    as $detail
                ) {

                    ConsignmentOutDetail::create(

                        array_merge(

                            [

                                'consignment_out_id' =>
                                    $header->id,

                            ],

                            $detail

                        )

                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Document Activity - CREATED
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $header,

                        'CREATED',

                        null,

                        'Draft',

                        'Consignment out created.'

                    );


                return $header;

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function updateConsignmentOut(
        ConsignmentOut $consignmentOut,
        array $data
    ): void {

        DB::transaction(
            function () use (
                $consignmentOut,
                $data
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Header
                |--------------------------------------------------------------------------
                */

                $consignmentOut =
                    ConsignmentOut::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $consignmentOut->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $consignmentOut->status,
                        [
                            'Draft',
                            'Rejected',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Only Draft or Rejected consignment out can be edited.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Capture Old Status
                |--------------------------------------------------------------------------
                */

                $oldStatus =
                    $consignmentOut->status;


                /*
                |--------------------------------------------------------------------------
                | Validate Details
                |--------------------------------------------------------------------------
                */

                if (
                    empty($data['details'])
                ) {

                    throw new \RuntimeException(
                        'Consignment out must have at least one detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Prepare Details
                |--------------------------------------------------------------------------
                */

                $preparedDetails = [];


                foreach (
                    $data['details']
                    as $detail
                ) {

                    $productVariant =
                        ProductVariant::query()
                            ->findOrFail(
                                $detail[
                                    'product_variant_id'
                                ]
                            );


                    $qty =
                        (float) (
                            $detail['qty']
                            ?? 0
                        );


                    if (
                        $qty <= 0
                    ) {

                        throw new \RuntimeException(
                            'Quantity must be greater than zero.'
                        );

                    }


                    $unitPrice =
                        (float) (
                            $detail['unit_price']
                            ?? 0
                        );


                    if (
                        $unitPrice < 0
                    ) {

                        throw new \RuntimeException(
                            'Consignment price cannot be negative.'
                        );

                    }


                    $totalPrice =
                        $qty *
                        $unitPrice;


                    $preparedDetails[] = [

                        'product_variant_id' =>
                            $productVariant->id,

                        'unit_id' =>
                            $detail['unit_id'],

                        'qty' =>
                            $qty,

                        'unit_price' =>
                            $unitPrice,

                        'total_price' =>
                            $totalPrice,

                    ];

                }


                /*
                |--------------------------------------------------------------------------
                | Update Header
                |--------------------------------------------------------------------------
                */

                $consignmentOut->update([

                    /*
                    |--------------------------------------------------------------------------
                    | Organization
                    |--------------------------------------------------------------------------
                    */

                    'company_id' =>
                        $data[
                            'company_id'
                        ],

                    'branch_id' =>
                        $data[
                            'branch_id'
                        ],

                    'warehouse_id' =>
                        $data[
                            'warehouse_id'
                        ],

                    'reseller_id' =>
                        $data[
                            'reseller_id'
                        ],

                    /*
                    |--------------------------------------------------------------------------
                    | Transaction
                    |--------------------------------------------------------------------------
                    */

                    'transaction_date' =>
                        $data[
                            'transaction_date'
                        ],

                    'reference_number' =>
                        ! empty(
                            trim(
                                $data[
                                    'reference_number'
                                    ] ?? ''
                            )
                        )
                            ? trim(
                                $data[
                                    'reference_number'
                                ]
                            )
                            : null,

                    /*
                    |--------------------------------------------------------------------------
                    | Notes
                    |--------------------------------------------------------------------------
                    */

                    'remarks' =>
                        $data[
                            'remarks'
                        ] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Audit
                    |--------------------------------------------------------------------------
                    */

                    'updated_by' =>
                        auth()->id(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Replace Details
                |--------------------------------------------------------------------------
                */

                $consignmentOut
                    ->details()
                    ->delete();


                foreach (
                    $preparedDetails
                    as $detail
                ) {

                    ConsignmentOutDetail::create(

                        array_merge(

                            [

                                'consignment_out_id' =>
                                    $consignmentOut->id,

                            ],

                            $detail

                        )

                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Rejected → Draft
                |--------------------------------------------------------------------------
                */

                if (
                    $oldStatus === 'Rejected'
                ) {

                    $consignmentOut->update([

                        'status' =>
                            'Draft',

                        'rejected_at' =>
                            null,

                        'rejected_by' =>
                            null,

                        'rejected_reason' =>
                            null,

                        'updated_by' =>
                            auth()->id(),

                    ]);


                    $this
                        ->documentActivityService
                        ->record(

                            $consignmentOut,

                            'RESUBMITTED',

                            'Rejected',

                            'Draft',

                            'Rejected consignment out was corrected and resubmitted.'

                        );

                }


                /*
                |--------------------------------------------------------------------------
                | Draft → Draft
                |--------------------------------------------------------------------------
                */

                else {

                    $this
                        ->documentActivityService
                        ->record(

                            $consignmentOut,

                            'UPDATED',

                            'Draft',

                            'Draft',

                            'Consignment out updated.'

                        );

                }

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submitConsignmentOut(
        ConsignmentOut $consignmentOut
    ): void {

        DB::transaction(
            function () use ($consignmentOut) {

                /*
                |--------------------------------------------------------------------------
                | Lock Header
                |--------------------------------------------------------------------------
                */

                $consignmentOut =
                    ConsignmentOut::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $consignmentOut->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    $consignmentOut->status !== 'Draft'
                ) {

                    throw new \RuntimeException(
                        'Only Draft consignment out can be submitted.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Details
                |--------------------------------------------------------------------------
                */

                if (
                    ! $consignmentOut
                        ->details()
                        ->exists()
                ) {

                    throw new \RuntimeException(
                        'Consignment out must have at least one detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Submit
                |--------------------------------------------------------------------------
                */

                $consignmentOut->update([

                    'status' =>
                        'Submitted',

                    'submitted_at' =>
                        now(),

                    'submitted_by' =>
                        auth()->id(),

                    'updated_by' =>
                        auth()->id(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Activity
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $consignmentOut,

                        'SUBMITTED',

                        'Draft',

                        'Submitted',

                        'Consignment out submitted for approval.'

                    );

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approveConsignmentOut(
        ConsignmentOut $consignmentOut
    ): void {

        DB::transaction(
            function () use ($consignmentOut) {

                /*
                |--------------------------------------------------------------------------
                | Lock Header
                |--------------------------------------------------------------------------
                */

                $consignmentOut =
                    ConsignmentOut::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $consignmentOut->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    $consignmentOut->status !== 'Submitted'
                ) {

                    throw new \RuntimeException(
                        'Only Submitted consignment out can be approved.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Approve
                |--------------------------------------------------------------------------
                */

                $consignmentOut->update([

                    'status' =>
                        'Approved',

                    'approved_at' =>
                        now(),

                    'approved_by' =>
                        auth()->id(),

                    'updated_by' =>
                        auth()->id(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Activity
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $consignmentOut,

                        'APPROVED',

                        'Submitted',

                        'Approved',

                        'Consignment out approved.'

                    );

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function rejectConsignmentOut(
        ConsignmentOut $consignmentOut,
        string $reason
    ): void {

        DB::transaction(
            function () use (
                $consignmentOut,
                $reason
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Header
                |--------------------------------------------------------------------------
                */

                $consignmentOut =
                    ConsignmentOut::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $consignmentOut->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    $consignmentOut->status !== 'Submitted'
                ) {

                    throw new \RuntimeException(
                        'Only Submitted consignment out can be rejected.'
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
                        'Rejection reason is required.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Reject
                |--------------------------------------------------------------------------
                */

                $consignmentOut->update([

                    'status' =>
                        'Rejected',

                    'rejected_at' =>
                        now(),

                    'rejected_by' =>
                        auth()->id(),

                    'rejected_reason' =>
                        $reason,

                    'updated_by' =>
                        auth()->id(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Activity
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $consignmentOut,

                        'REJECTED',

                        'Submitted',

                        'Rejected',

                        'Consignment out rejected.',

                        [

                            'reason' =>
                                $reason,

                        ]

                    );

            }
        );
    }

/*
|--------------------------------------------------------------------------
| Post
|--------------------------------------------------------------------------
*/

public function postConsignmentOut(
    ConsignmentOut $consignmentOut
): void {

    DB::transaction(
        function () use ($consignmentOut) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $consignmentOut =
                ConsignmentOut::query()
                    ->with('details')
                    ->lockForUpdate()
                    ->findOrFail(
                        $consignmentOut->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $consignmentOut->status !== 'Approved'
            ) {

                throw new \RuntimeException(
                    'Only Approved consignment out can be posted.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Details
            |--------------------------------------------------------------------------
            */

            $details =
                $consignmentOut->details;

            if (
                $details->isEmpty()
            ) {

                throw new \RuntimeException(
                    'Consignment out must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Prepare Inventory Movements
            |--------------------------------------------------------------------------
            */

            $inventoryLines = [];


            foreach (
                $details
                as $detail
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Source Stock
                |--------------------------------------------------------------------------
                */

                $stock =
                    ProductStock::query()
                        ->where(
                            'company_id',
                            $consignmentOut->company_id
                        )
                        ->where(
                            'branch_id',
                            $consignmentOut->branch_id
                        )
                        ->where(
                            'warehouse_id',
                            $consignmentOut->warehouse_id
                        )
                        ->where(
                            'product_variant_id',
                            $detail->product_variant_id
                        )
                        ->where(
                            'unit_id',
                            $detail->unit_id
                        )
                        ->whereNull(
                            'reseller_id'
                        )
                        ->lockForUpdate()
                        ->first();


                if (! $stock) {

                    throw new \RuntimeException(
                        'Source warehouse stock was not found for product variant ' .
                        $detail->product_variant_id . '.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Available Stock
                |--------------------------------------------------------------------------
                */

                if (
                    (float) $stock->available_qty <
                    (float) $detail->qty
                ) {

                    throw new \RuntimeException(
                        'Insufficient stock for product variant ' .
                        $detail->product_variant_id . '.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Inventory Cost
                |--------------------------------------------------------------------------
                */

                $unitCost =
                    (float) $stock->average_cost;


                $totalCost =
                    (float) $detail->qty *
                    $unitCost;


               $inventoryLines[] = [

                    'product_variant_id' =>
                        $detail->product_variant_id,

                    'unit_id' =>
                        $detail->unit_id,

                    'qty' =>
                        (float) $detail->qty,

                    'unit_cost' =>
                        $unitCost,

                    'total_cost' =>
                        $totalCost,

                    'reseller_unit_price' =>
                        (float) $detail->unit_price,

                    'consignment_out_detail_id' =>
                        $detail->id,

                ];

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
                        $consignmentOut->company_id
                    )
                    ->whereDate(
                        'start_date',
                        '<=',
                        $consignmentOut->transaction_date
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        $consignmentOut->transaction_date
                    )
                    ->where(
                        'status',
                        'Open'
                    )
                    ->first();


            if (! $fiscalYear) {

                throw new \RuntimeException(
                    'No open fiscal year found for consignment out date.'
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
                        $consignmentOut->company_id
                    )
                    ->where(
                        'fiscal_year_id',
                        $fiscalYear->id
                    )
                    ->whereDate(
                        'start_date',
                        '<=',
                        $consignmentOut->transaction_date
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        $consignmentOut->transaction_date
                    )
                    ->where(
                        'status',
                        'Open'
                    )
                    ->first();


            if (! $accountingPeriod) {

                throw new \RuntimeException(
                    'No open accounting period found for consignment out date.'
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
                        $consignmentOut->company_id
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

            $consignmentInventoryAccount =
                $this->accountMappingService
                    ->getAccount(
                        $consignmentOut->company_id,
                        'inventory_consignment'
                    );


            $warehouseInventoryAccount =
                $this->accountMappingService
                    ->getAccount(
                        $consignmentOut->company_id,
                        'inventory_merchandise'
                    );


            /*
            |--------------------------------------------------------------------------
            | Calculate Total Inventory Cost
            |--------------------------------------------------------------------------
            */

            $totalInventoryCost = 0;


            foreach (
                $inventoryLines
                as $line
            ) {

                $totalInventoryCost +=
                    $line['total_cost'];

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Cost
            |--------------------------------------------------------------------------
            */

            if (
                $totalInventoryCost < 0
            ) {

                throw new \RuntimeException(
                    'Consignment inventory cost cannot be negative.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Journal Lines
            |--------------------------------------------------------------------------
            */

            $lines = [];


            /*
            |--------------------------------------------------------------------------
            | Inventory - Consignment
            |--------------------------------------------------------------------------
            */

            if (
                $totalInventoryCost > 0
            ) {

                $lines[] = [

                    'account_id' =>
                        $consignmentInventoryAccount->id,

                    'debit' =>
                        round(
                            $totalInventoryCost,
                            2
                        ),

                    'credit' =>
                        0,

                    'description' =>
                        'Inventory transferred to consignment for ' .
                        $consignmentOut->consignment_out_number,

                ];


                /*
                |--------------------------------------------------------------------------
                | Inventory - Warehouse
                |--------------------------------------------------------------------------
                */

                $lines[] = [

                    'account_id' =>
                        $warehouseInventoryAccount->id,

                    'debit' =>
                        0,

                    'credit' =>
                        round(
                            $totalInventoryCost,
                            2
                        ),

                    'description' =>
                        'Inventory transferred from warehouse for ' .
                        $consignmentOut->consignment_out_number,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Create Journal Entry
            |--------------------------------------------------------------------------
            */

            $journalEntry =
                $this->journalEntryService
                    ->create([

                        'company_id' =>
                            $consignmentOut->company_id,

                        'branch_id' =>
                            $consignmentOut->branch_id,

                        'accounting_journal_id' =>
                            $journal->id,

                        'fiscal_year_id' =>
                            $fiscalYear->id,

                        'accounting_period_id' =>
                            $accountingPeriod->id,

                        'entry_date' =>
                            $consignmentOut->transaction_date,

                        'reference_type' =>
                            'CONSIGNMENT_OUT',

                        'reference_id' =>
                            $consignmentOut->id,

                        'reference_number' =>
                            $consignmentOut->consignment_out_number,

                        'description' =>
                            'Consignment out ' .
                            $consignmentOut->consignment_out_number,

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
            | Inventory Movement
            |--------------------------------------------------------------------------
            */

            foreach (
                $inventoryLines
                as $line
            ) {

                /*
                |--------------------------------------------------------------------------
                | Warehouse → OUT
                |--------------------------------------------------------------------------
                */

                $this
                    ->inventoryService
                    ->stockOut([

                        'company_id' =>
                            $consignmentOut->company_id,

                        'branch_id' =>
                            $consignmentOut->branch_id,

                        'warehouse_id' =>
                            $consignmentOut->warehouse_id,

                        'product_variant_id' =>
                            $line[
                                'product_variant_id'
                            ],

                        'unit_id' =>
                            $line[
                                'unit_id'
                            ],

                        'qty' =>
                            $line['qty'],

                        'unit_cost' =>
                            $line['unit_cost'],

                        'transaction_date' =>
                            $consignmentOut->transaction_date,

                        'reference_type' =>
                            'CONSIGNMENT_OUT',

                        'reference_id' =>
                            $consignmentOut->id,

                        'reference_number' =>
                            $consignmentOut
                                ->consignment_out_number,

                        'description' =>
                            'Consignment out from warehouse.',

                        'created_by' =>
                            auth()->id(),

                        'reseller_id' =>
                            null,

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Warehouse → Reseller Consignment Stock
                |--------------------------------------------------------------------------
                */

                $this
                    ->inventoryService
                    ->stockIn([

                        'company_id' =>
                            $consignmentOut->company_id,

                        'branch_id' =>
                            $consignmentOut->branch_id,

                        'warehouse_id' =>
                            $consignmentOut->warehouse_id,

                        'product_variant_id' =>
                            $line[
                                'product_variant_id'
                            ],

                        'unit_id' =>
                            $line[
                                'unit_id'
                            ],

                        'qty' =>
                            $line['qty'],

                        'unit_cost' =>
                            $line['unit_cost'],

                        'transaction_date' =>
                            $consignmentOut->transaction_date,

                        'reference_type' =>
                            'CONSIGNMENT_OUT',

                        'reference_id' =>
                            $consignmentOut->id,

                        'reference_number' =>
                            $consignmentOut
                                ->consignment_out_number,

                        'description' =>
                            'Consignment stock received by reseller.',

                        'created_by' =>
                            auth()->id(),

                        'reseller_id' =>
                            $consignmentOut->reseller_id,

                    ]);
                    /*
                        |--------------------------------------------------------------------------
                        | Create Reseller Stock Price Layer
                        |--------------------------------------------------------------------------
                        */

                        ResellerStockPriceLayer::create([

                            'company_id' =>
                                $consignmentOut->company_id,

                            'branch_id' =>
                                $consignmentOut->branch_id,

                            'warehouse_id' =>
                                $consignmentOut->warehouse_id,

                            'reseller_id' =>
                                $consignmentOut->reseller_id,

                            'product_variant_id' =>
                                $line[
                                    'product_variant_id'
                                ],

                            'unit_id' =>
                                $line[
                                    'unit_id'
                                ],

                            'consignment_out_id' =>
                                $consignmentOut->id,

                            'consignment_out_detail_id' =>
                                $line[
                                    'consignment_out_detail_id'
                                ],

                            'unit_price' =>
                                round(
                                    $line[
                                        'reseller_unit_price'
                                    ],
                                    2
                                ),

                            'original_qty' =>
                                $line['qty'],

                            'remaining_qty' =>
                                $line['qty'],

                            'status' =>
                                'Open',

                        ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Mark Posted
            |--------------------------------------------------------------------------
            */

            $consignmentOut->update([

                'status' =>
                    'Posted',

                'posting_date' =>
                    now(),

                'posted_at' =>
                    now(),

                'posted_by' =>
                    auth()->id(),

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

                    $consignmentOut,

                    'POSTED',

                    'Approved',

                    'Posted',

                    'Consignment out posted.'

                );

        }
    );
}
/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

public function cancelConsignmentOut(
    ConsignmentOut $consignmentOut,
    string $reason
): void {

    DB::transaction(
        function () use (
            $consignmentOut,
            $reason
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $consignmentOut =
                ConsignmentOut::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $consignmentOut->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                ! in_array(
                    $consignmentOut->status,
                    [
                        'Draft',
                        'Submitted',
                        'Approved',
                        'Posted',
                    ],
                    true
                )
            ) {

                throw new \RuntimeException(
                    'Consignment out cannot be cancelled in its current status.'
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
            | Capture Old Status
            |--------------------------------------------------------------------------
            */

            $oldStatus =
                $consignmentOut->status;


            /*
            |--------------------------------------------------------------------------
            | Posted → Reverse Inventory
            |--------------------------------------------------------------------------
            */

            if (
                $oldStatus === 'Posted'
            ) {

                /*
                |--------------------------------------------------------------------------
                | Load Original Inventory Movements
                |--------------------------------------------------------------------------
                */

                $movements =
                    $consignmentOut
                        ->movements()
                        ->lockForUpdate()
                        ->get();


                if (
                    $movements->isEmpty()
                ) {

                    throw new \RuntimeException(
                        'Posted consignment out has no inventory movements to reverse.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Reverse Each Movement
                |--------------------------------------------------------------------------
                */

                foreach (
                    $movements
                    as $movement
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Original Warehouse OUT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (float) $movement->qty_out > 0
                        &&
                        is_null(
                            $movement->reseller_id
                        )
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
                                    'CONSIGNMENT_OUT_CANCEL',

                                'reference_id' =>
                                    $consignmentOut->id,

                                'reference_number' =>
                                    $consignmentOut
                                        ->consignment_out_number,

                                'description' =>
                                    'Consignment out cancellation - warehouse stock reversal.',

                                'reseller_id' =>
                                    null,

                            ]);
                            

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Original Consignment IN
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (float) $movement->qty_in > 0
                        &&
                        ! is_null(
                            $movement->reseller_id
                        )
                    ) {

                        $this
                            ->inventoryService
                            ->stockOut([

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
                                    $movement->qty_in,

                                'unit_cost' =>
                                    $movement->unit_cost,

                                'total_cost' =>
                                    $movement->total_cost,

                                'transaction_date' =>
                                    now(),

                                'reference_type' =>
                                    'CONSIGNMENT_OUT_CANCEL',

                                'reference_id' =>
                                    $consignmentOut->id,

                                'reference_number' =>
                                    $consignmentOut
                                        ->consignment_out_number,

                                'description' =>
                                    'Consignment out cancellation - reseller consignment stock reversal.',

                                'reseller_id' =>
                                    $movement->reseller_id,

                            ]);

                    }

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
                            $consignmentOut->company_id
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
                        'No open fiscal year found for cancellation.'
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
                            $consignmentOut->company_id
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
                        'No open accounting period found for cancellation.'
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
                            $consignmentOut->company_id
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

                $consignmentInventoryAccount =
                    $this
                        ->accountMappingService
                        ->getAccount(
                            $consignmentOut->company_id,
                            'inventory_consignment'
                        );


                $warehouseInventoryAccount =
                    $this
                        ->accountMappingService
                        ->getAccount(
                            $consignmentOut->company_id,
                            'inventory_merchandise'
                        );


                /*
                |--------------------------------------------------------------------------
                | Calculate Reversal Cost
                |--------------------------------------------------------------------------
                */

                $totalInventoryCost = 0;


                foreach (
                    $movements
                    as $movement
                ) {

                    if (
                        (float) $movement->qty_out > 0
                    ) {

                        $totalInventoryCost +=
                            (float) $movement->total_cost;

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | Journal Lines
                |--------------------------------------------------------------------------
                */

                $lines = [];


                if (
                    $totalInventoryCost > 0
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Inventory - Warehouse
                    |--------------------------------------------------------------------------
                    */

                    $lines[] = [

                        'account_id' =>
                            $warehouseInventoryAccount->id,

                        'debit' =>
                            round(
                                $totalInventoryCost,
                                2
                            ),

                        'credit' =>
                            0,

                        'description' =>
                            'Warehouse inventory reversal for cancelled consignment out ' .
                            $consignmentOut
                                ->consignment_out_number,

                    ];


                    /*
                    |--------------------------------------------------------------------------
                    | Inventory - Consignment
                    |--------------------------------------------------------------------------
                    */

                    $lines[] = [

                        'account_id' =>
                            $consignmentInventoryAccount->id,

                        'debit' =>
                            0,

                        'credit' =>
                            round(
                                $totalInventoryCost,
                                2
                            ),

                        'description' =>
                            'Consignment inventory reversal for cancelled consignment out ' .
                            $consignmentOut
                                ->consignment_out_number,

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
                                $consignmentOut->company_id,

                            'branch_id' =>
                                $consignmentOut->branch_id,

                            'accounting_journal_id' =>
                                $journal->id,

                            'fiscal_year_id' =>
                                $fiscalYear->id,

                            'accounting_period_id' =>
                                $accountingPeriod->id,

                            'entry_date' =>
                                now(),

                            'reference_type' =>
                                'CONSIGNMENT_OUT_CANCEL',

                            'reference_id' =>
                                $consignmentOut->id,

                            'reference_number' =>
                                $consignmentOut
                                    ->consignment_out_number,

                            'description' =>
                                'Cancellation of consignment out ' .
                                $consignmentOut
                                    ->consignment_out_number,

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

            }


            /*
            |--------------------------------------------------------------------------
            | Mark Cancelled
            |--------------------------------------------------------------------------
            */

            $consignmentOut->update([

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

                    $consignmentOut,

                    'CANCELLED',

                    $oldStatus,

                    'Cancelled',

                    'Consignment out cancelled.',

                    [

                        'reason' =>
                            $reason,

                    ]

                );

        }
    );
}
/*
|--------------------------------------------------------------------------
| Duplicate
|--------------------------------------------------------------------------
*/

public function duplicateConsignmentOut(
    ConsignmentOut $consignmentOut
): ConsignmentOut {

    return DB::transaction(
        function () use ($consignmentOut) {

            /*
            |--------------------------------------------------------------------------
            | Load Details
            |--------------------------------------------------------------------------
            */

            $consignmentOut
                ->load('details');


            /*
            |--------------------------------------------------------------------------
            | Create Duplicate Header
            |--------------------------------------------------------------------------
            */

            $duplicate =
                ConsignmentOut::create([

                    'company_id' =>
                        $consignmentOut
                            ->company_id,

                    'branch_id' =>
                        $consignmentOut
                            ->branch_id,

                    'warehouse_id' =>
                        $consignmentOut
                            ->warehouse_id,

                    'reseller_id' =>
                        $consignmentOut
                            ->reseller_id,

                    'consignment_out_number' =>
                        $this
                            ->codeGeneratorService
                            ->next(
                                'consignment_out'
                            ),

                    'transaction_date' =>
                        $consignmentOut
                            ->transaction_date,

                    'reference_number' =>
                        $consignmentOut
                            ->reference_number,

                    /*
                    |--------------------------------------------------------------------------
                    | Reset Posting
                    |--------------------------------------------------------------------------
                    */

                    'posting_date' =>
                        null,

                    'status' =>
                        'Draft',

                    /*
                    |--------------------------------------------------------------------------
                    | Information
                    |--------------------------------------------------------------------------
                    */

                    'remarks' =>
                        $consignmentOut
                            ->remarks
                        ? 'Copy - ' .
                            $consignmentOut
                                ->remarks
                        : 'Copy Consignment Out',

                    /*
                    |--------------------------------------------------------------------------
                    | Audit
                    |--------------------------------------------------------------------------
                    */

                    'created_by' =>
                        auth()->id(),

                ]);


            /*
            |--------------------------------------------------------------------------
            | Duplicate Details
            |--------------------------------------------------------------------------
            */

            foreach (
                $consignmentOut->details
                as $detail
            ) {

                ConsignmentOutDetail::create([

                    'consignment_out_id' =>
                        $duplicate->id,

                    'product_variant_id' =>
                        $detail
                            ->product_variant_id,

                    'unit_id' =>
                        $detail
                            ->unit_id,

                    'qty' =>
                        $detail
                            ->qty,

                    'unit_price' =>
                        $detail
                            ->unit_price,

                    'total_price' =>
                        $detail
                            ->total_price,

                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Activity
            |--------------------------------------------------------------------------
            */

            $this
                ->documentActivityService
                ->record(

                    $duplicate,

                    'CREATED',

                    null,

                    'Draft',

                    'Consignment out duplicated.'

                );


            return $duplicate;

        }
    );
}
/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

public function deleteConsignmentOuts(
    array $ids
): void {

    DB::transaction(
        function () use ($ids) {

            $consignmentOuts =
                ConsignmentOut::query()
                    ->whereIn(
                        'id',
                        $ids
                    )
                    ->lockForUpdate()
                    ->get();


            foreach (
                $consignmentOuts
                as $consignmentOut
            ) {

                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $consignmentOut->status,
                        [
                            'Draft',
                            'Rejected',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Submitted or processed consignment out cannot be deleted.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Delete Details
                |--------------------------------------------------------------------------
                */

                $consignmentOut
                    ->details()
                    ->delete();


                /*
                |--------------------------------------------------------------------------
                | Soft Delete Header
                |--------------------------------------------------------------------------
                */

                $consignmentOut->delete();

            }

        }
    );
}
}