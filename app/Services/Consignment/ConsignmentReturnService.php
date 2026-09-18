<?php

namespace App\Services\Consignment;

use App\Models\Inventory\ProductStock;
use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\FiscalYear;
use App\Models\Reseller\ConsignmentReturn\ConsignmentReturnDetail;
use App\Models\Reseller\ConsignmentReturn\ConsignmentReturnHeader;
use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;
use App\Services\Accounting\AccountMappingService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use App\Services\Inventory\InventoryService;

use Illuminate\Support\Facades\DB;

class ConsignmentReturnService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected DocumentActivityService $documentActivityService;

    protected InventoryService $inventoryService;

    protected AccountMappingService $accountMappingService;

    protected JournalEntryService $journalEntryService;


    public function __construct(
        CodeGeneratorService $codeGeneratorService,
        DocumentActivityService $documentActivityService,
        InventoryService $inventoryService,
        AccountMappingService $accountMappingService,
        JournalEntryService $journalEntryService
    ) {

        $this->codeGeneratorService =
            $codeGeneratorService;

        $this->documentActivityService =
            $documentActivityService;

        $this->inventoryService =
            $inventoryService;

        $this->accountMappingService =
            $accountMappingService;

        $this->journalEntryService =
            $journalEntryService;

    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function createConsignmentReturn(
        array $data
    ): ConsignmentReturnHeader {

        return DB::transaction(
            function () use ($data) {

                /*
                |--------------------------------------------------------------------------
                | Validate Details
                |--------------------------------------------------------------------------
                */

                if (
                    empty(
                        $data['details']
                        ?? []
                    )
                ) {

                    throw new \RuntimeException(
                        'Consignment return must have at least one detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Branch
                |--------------------------------------------------------------------------
                */

                $branch =
                    \App\Models\MasterData\Branch::query()
                        ->findOrFail(
                            $data['branch_id']
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Warehouse
                |--------------------------------------------------------------------------
                */

                $warehouse =
                    \App\Models\MasterData\Warehouse::query()
                        ->findOrFail(
                            $data['warehouse_id']
                        );


                if (
                    (int) $warehouse->branch_id
                    !==
                    (int) $branch->id
                ) {

                    throw new \RuntimeException(
                        'Warehouse does not belong to selected branch.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Reseller
                |--------------------------------------------------------------------------
                */

                $reseller =
                    \App\Models\Reseller\Reseller::query()
                        ->findOrFail(
                            $data['reseller_id']
                        );


                if (
                    (int) $reseller->company_id
                    !==
                    (int) $branch->company_id
                ) {

                    throw new \RuntimeException(
                        'Reseller does not belong to selected company.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Optional Settlement
                |--------------------------------------------------------------------------
                */

                if (
                    ! empty(
                        $data['settlement_header_id']
                    )
                ) {

                    $settlement =
                        ConsignmentSettlementHeader::query()
                            ->findOrFail(
                                $data[
                                    'settlement_header_id'
                                ]
                            );


                    if (
                        (int) $settlement->company_id
                        !==
                        (int) $branch->company_id
                    ) {

                        throw new \RuntimeException(
                            'Settlement does not belong to selected company.'
                        );

                    }


                    if (
                        (int) $settlement->branch_id
                        !==
                        (int) $branch->id
                    ) {

                        throw new \RuntimeException(
                            'Settlement does not belong to selected branch.'
                        );

                    }


                    if (
                        (int) $settlement->reseller_id
                        !==
                        (int) $reseller->id
                    ) {

                        throw new \RuntimeException(
                            'Settlement does not belong to selected reseller.'
                        );

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | Generate Number
                |--------------------------------------------------------------------------
                */

                $number =
                    $this->codeGeneratorService
                        ->next(
                            'CONSIGNMENT_RETURN'
                        );


                /*
                |--------------------------------------------------------------------------
                | Create Header
                |--------------------------------------------------------------------------
                */

                $consignmentReturn =
                    ConsignmentReturnHeader::create([

                        'company_id' =>
                            $branch->company_id,

                        'branch_id' =>
                            $branch->id,

                        'return_number' =>
                            $number,

                        'settlement_header_id' =>
                            $data[
                                'settlement_header_id'
                            ]
                            ?? null,

                        'reseller_id' =>
                            $reseller->id,

                        'warehouse_id' =>
                            $warehouse->id,

                        'return_date' =>
                            $data['return_date']
                            ?? now(),

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
                    $data['details']
                    as $detail
                ) {

                    $returnedQty =
                        (float)
                        (
                            $detail['returned_qty']
                            ?? 0
                        );


                    if (
                        $returnedQty <= 0
                    ) {

                        throw new \RuntimeException(
                            'Return quantity must be greater than zero.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Reseller Consignment Stock
                    |--------------------------------------------------------------------------
                    */

                    $productStock =
                        ProductStock::query()
                            ->where(
                                'company_id',
                                $branch->company_id
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
                                'reseller_id',
                                $reseller->id
                            )
                            ->where(
                                'product_variant_id',
                                $detail['product_variant_id']
                            )
                            ->where(
                                'unit_id',
                                $detail['unit_id']
                            )
                            ->lockForUpdate()
                            ->first();


                    if (! $productStock) {

                        throw new \RuntimeException(
                            'Consignment stock not found for selected product.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Available Stock
                    |--------------------------------------------------------------------------
                    */

                    $availableQty =
                        (float)
                        $productStock->available_qty;


                    if (
                        $returnedQty >
                        $availableQty
                    ) {

                        throw new \RuntimeException(
                            'Return quantity exceeds available consignment stock.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Unit Cost
                    |--------------------------------------------------------------------------
                    */

                    $unitCost =
                        (float)
                        $productStock->average_cost;


                    if (
                        $unitCost < 0
                    ) {

                        throw new \RuntimeException(
                            'Consignment stock average cost cannot be negative.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Total Cost
                    |--------------------------------------------------------------------------
                    */

                    $totalCost =
                        round(
                            $returnedQty *
                            $unitCost,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Create Detail
                    |--------------------------------------------------------------------------
                    */

                    ConsignmentReturnDetail::create([

                        'consignment_return_header_id' =>
                            $consignmentReturn->id,

                        'product_variant_id' =>
                            $detail['product_variant_id'],

                        'unit_id' =>
                            $detail['unit_id'],

                        'returned_qty' =>
                            $returnedQty,

                        'unit_cost' =>
                            $unitCost,

                        'total_cost' =>
                            $totalCost,

                        'remarks' =>
                            $detail['remarks']
                            ?? null,

                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Document Activity
                |--------------------------------------------------------------------------
                */

                $this->documentActivityService
                    ->record(

                        $consignmentReturn,

                        'CREATED',

                        null,

                        'Draft',

                        'Consignment return created.'

                    );


                return $consignmentReturn
                    ->fresh([
                        'details',
                    ]);

            }
        );

    }

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

public function updateConsignmentReturn(
    ConsignmentReturnHeader $consignmentReturn,
    array $data
): ConsignmentReturnHeader {

    return DB::transaction(
        function () use (
            $consignmentReturn,
            $data
        ) {

            $consignmentReturn =
                ConsignmentReturnHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $consignmentReturn->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                ! in_array(
                    $consignmentReturn->status,
                    [
                        'Draft',
                        'Rejected',
                    ],
                    true
                )
            ) {

                throw new \RuntimeException(
                    'Only Draft or Rejected consignment return can be updated.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Details
            |--------------------------------------------------------------------------
            */

            if (
                empty(
                    $data['details']
                    ?? []
                )
            ) {

                throw new \RuntimeException(
                    'Consignment return must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Branch
            |--------------------------------------------------------------------------
            */

            $branch =
                \App\Models\MasterData\Branch::query()
                    ->findOrFail(
                        $data['branch_id']
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Warehouse
            |--------------------------------------------------------------------------
            */

            $warehouse =
                \App\Models\MasterData\Warehouse::query()
                    ->findOrFail(
                        $data['warehouse_id']
                    );


            if (
                (int) $warehouse->branch_id
                !==
                (int) $branch->id
            ) {

                throw new \RuntimeException(
                    'Warehouse does not belong to selected branch.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Reseller
            |--------------------------------------------------------------------------
            */

            $reseller =
                \App\Models\Reseller\Reseller::query()
                    ->findOrFail(
                        $data['reseller_id']
                    );


            if (
                (int) $reseller->company_id
                !==
                (int) $branch->company_id
            ) {

                throw new \RuntimeException(
                    'Reseller does not belong to selected company.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Optional Settlement
            |--------------------------------------------------------------------------
            */

            if (
                ! empty(
                    $data['settlement_header_id']
                )
            ) {

                $settlement =
                    ConsignmentSettlementHeader::query()
                        ->findOrFail(
                            $data[
                                'settlement_header_id'
                            ]
                        );


                if (
                    (int) $settlement->company_id
                    !==
                    (int) $branch->company_id
                ) {

                    throw new \RuntimeException(
                        'Settlement does not belong to selected company.'
                    );

                }


                if (
                    (int) $settlement->branch_id
                    !==
                    (int) $branch->id
                ) {

                    throw new \RuntimeException(
                        'Settlement does not belong to selected branch.'
                    );

                }


                if (
                    (int) $settlement->reseller_id
                    !==
                    (int) $reseller->id
                ) {

                    throw new \RuntimeException(
                        'Settlement does not belong to selected reseller.'
                    );

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Update Header
            |--------------------------------------------------------------------------
            */

            $previousStatus =
                $consignmentReturn->status;


            $consignmentReturn->update([

                'company_id' =>
                    $branch->company_id,

                'branch_id' =>
                    $branch->id,

                'settlement_header_id' =>
                    $data[
                        'settlement_header_id'
                    ]
                    ?? null,

                'reseller_id' =>
                    $reseller->id,

                'warehouse_id' =>
                    $warehouse->id,

                'return_date' =>
                    $data['return_date']
                    ?? $consignmentReturn->return_date,

                'remarks' =>
                    $data['remarks']
                    ?? null,

                'status' =>
                    'Draft',

                'updated_by' =>
                    auth()->id(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | Delete Existing Details
            |--------------------------------------------------------------------------
            */

            $consignmentReturn
                ->details()
                ->delete();


            /*
            |--------------------------------------------------------------------------
            | Create Details Again
            |--------------------------------------------------------------------------
            */

            foreach (
                $data['details']
                as $detail
            ) {

                $returnedQty =
                    (float)
                    (
                        $detail['returned_qty']
                        ?? 0
                    );


                if (
                    $returnedQty <= 0
                ) {

                    throw new \RuntimeException(
                        'Return quantity must be greater than zero.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Lock Reseller Consignment Stock
                |--------------------------------------------------------------------------
                */

                $productStock =
                    ProductStock::query()
                        ->where(
                            'company_id',
                            $branch->company_id
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
                            'reseller_id',
                            $reseller->id
                        )
                        ->where(
                            'product_variant_id',
                            $detail[
                                'product_variant_id'
                            ]
                        )
                        ->where(
                            'unit_id',
                            $detail['unit_id']
                        )
                        ->lockForUpdate()
                        ->first();


                if (! $productStock) {

                    throw new \RuntimeException(
                        'Consignment stock not found for selected product.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Available Stock
                |--------------------------------------------------------------------------
                */

                $availableQty =
                    (float)
                    $productStock->available_qty;


                if (
                    $returnedQty >
                    $availableQty
                ) {

                    throw new \RuntimeException(
                        'Return quantity exceeds available consignment stock.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Unit Cost
                |--------------------------------------------------------------------------
                */

                $unitCost =
                    (float)
                    $productStock->average_cost;


                if (
                    $unitCost < 0
                ) {

                    throw new \RuntimeException(
                        'Consignment stock average cost cannot be negative.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Total Cost
                |--------------------------------------------------------------------------
                */

                $totalCost =
                    round(
                        $returnedQty *
                        $unitCost,
                        2
                    );


                /*
                |--------------------------------------------------------------------------
                | Create Detail
                |--------------------------------------------------------------------------
                */

                ConsignmentReturnDetail::create([

                    'consignment_return_header_id' =>
                        $consignmentReturn->id,

                    'product_variant_id' =>
                        $detail[
                            'product_variant_id'
                        ],

                    'unit_id' =>
                        $detail['unit_id'],

                    'returned_qty' =>
                        $returnedQty,

                    'unit_cost' =>
                        $unitCost,

                    'total_cost' =>
                        $totalCost,

                    'remarks' =>
                        $detail['remarks']
                        ?? null,

                ]);

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Details
            |--------------------------------------------------------------------------
            */

            if (
                $consignmentReturn
                    ->details()
                    ->count()
                <= 0
            ) {

                throw new \RuntimeException(
                    'Consignment return must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Document Activity
            |--------------------------------------------------------------------------
            */

            $this->documentActivityService
                ->record(

                    $consignmentReturn,

                    'UPDATED',

                    $previousStatus,

                    'Draft',

                    'Consignment return updated.'

                );


            return $consignmentReturn
                ->fresh([
                    'details',
                ]);

        }
    );

}


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submitConsignmentReturn(
        ConsignmentReturnHeader $consignmentReturn
    ): void {

        DB::transaction(
            function () use ($consignmentReturn) {

                $consignmentReturn =
                    ConsignmentReturnHeader::query()
                        ->with('details')
                        ->lockForUpdate()
                        ->findOrFail(
                            $consignmentReturn->id
                        );


                if (
                    $consignmentReturn->status !== 'Draft'
                ) {

                    throw new \RuntimeException(
                        'Only Draft consignment return can be submitted.'
                    );

                }


                if (
                    $consignmentReturn
                        ->details
                        ->isEmpty()
                ) {

                    throw new \RuntimeException(
                        'Consignment return must have at least one detail.'
                    );

                }


                $consignmentReturn->update([

                    'status' =>
                        'Submitted',

                    'updated_by' =>
                        auth()->id(),

                ]);


                $this->documentActivityService
                    ->record(

                        $consignmentReturn,

                        'SUBMITTED',

                        'Draft',

                        'Submitted',

                        'Consignment return submitted.'

                    );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approveConsignmentReturn(
        ConsignmentReturnHeader $consignmentReturn
    ): void {

        DB::transaction(
            function () use ($consignmentReturn) {

                $consignmentReturn =
                    ConsignmentReturnHeader::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $consignmentReturn->id
                        );


                if (
                    $consignmentReturn->status !== 'Submitted'
                ) {

                    throw new \RuntimeException(
                        'Only Submitted consignment return can be approved.'
                    );

                }


                if (
                    $consignmentReturn
                        ->details()
                        ->count()
                    <= 0
                ) {

                    throw new \RuntimeException(
                        'Consignment return must have at least one detail.'
                    );

                }


                $consignmentReturn->update([

                    'status' =>
                        'Approved',

                    'approved_at' =>
                        now(),

                    'approved_by' =>
                        auth()->id(),

                    'updated_by' =>
                        auth()->id(),

                ]);


                $this->documentActivityService
                    ->record(

                        $consignmentReturn,

                        'APPROVED',

                        'Submitted',

                        'Approved',

                        'Consignment return approved.'

                    );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function reject(
        ConsignmentReturnHeader $consignmentReturn,
        string $reason
    ): void {

        DB::transaction(
            function () use (
                $consignmentReturn,
                $reason
            ) {

                $consignmentReturn =
                    ConsignmentReturnHeader::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $consignmentReturn->id
                        );


                if (
                    $consignmentReturn->status !== 'Submitted'
                ) {

                    throw new \RuntimeException(
                        'Only Submitted consignment return can be rejected.'
                    );

                }


                if (
                    trim($reason) === ''
                ) {

                    throw new \RuntimeException(
                        'Rejection reason is required.'
                    );

                }


                $consignmentReturn->update([

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


                $this->documentActivityService
                    ->record(

                        $consignmentReturn,

                        'REJECTED',

                        'Submitted',

                        'Rejected',

                        $reason

                    );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Post
    |--------------------------------------------------------------------------
    */

    public function postConsignmentReturn(
        ConsignmentReturnHeader $consignmentReturn
    ): void {

        DB::transaction(
            function () use ($consignmentReturn) {

                /*
                |--------------------------------------------------------------------------
                | Lock Header
                |--------------------------------------------------------------------------
                */

                $consignmentReturn =
                    ConsignmentReturnHeader::query()
                        ->with('details')
                        ->lockForUpdate()
                        ->findOrFail(
                            $consignmentReturn->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    $consignmentReturn->status !== 'Approved'
                ) {

                    throw new \RuntimeException(
                        'Only Approved consignment return can be posted.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Details
                |--------------------------------------------------------------------------
                */

                $details =
                    $consignmentReturn->details;

                if (
                    $details->isEmpty()
                ) {

                    throw new \RuntimeException(
                        'Consignment return must have at least one detail.'
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
                            $consignmentReturn->company_id
                        )
                        ->whereDate(
                            'start_date',
                            '<=',
                            $consignmentReturn->return_date
                        )
                        ->whereDate(
                            'end_date',
                            '>=',
                            $consignmentReturn->return_date
                        )
                        ->where(
                            'status',
                            'Open'
                        )
                        ->first();


                if (! $fiscalYear) {

                    throw new \RuntimeException(
                        'No open fiscal year found for consignment return date.'
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
                            $consignmentReturn->company_id
                        )
                        ->where(
                            'fiscal_year_id',
                            $fiscalYear->id
                        )
                        ->whereDate(
                            'start_date',
                            '<=',
                            $consignmentReturn->return_date
                        )
                        ->whereDate(
                            'end_date',
                            '>=',
                            $consignmentReturn->return_date
                        )
                        ->where(
                            'status',
                            'Open'
                        )
                        ->first();


                if (! $accountingPeriod) {

                    throw new \RuntimeException(
                        'No open accounting period found for consignment return date.'
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
                            $consignmentReturn->company_id
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

                $inventoryMerchandiseAccount =
                    $this->accountMappingService
                        ->getAccount(
                            $consignmentReturn->company_id,
                            'inventory_merchandise'
                        );


                $inventoryConsignmentAccount =
                    $this->accountMappingService
                        ->getAccount(
                            $consignmentReturn->company_id,
                            'inventory_consignment'
                        );


                /*
                |--------------------------------------------------------------------------
                | Accounting Amount
                |--------------------------------------------------------------------------
                */

                $inventoryAmount =
                    0;


                /*
                |--------------------------------------------------------------------------
                | Process Details
                |--------------------------------------------------------------------------
                */

                foreach (
                    $details
                    as $detail
                ) {

                    $returnedQty =
                        (float)
                        $detail->returned_qty;


                    if (
                        $returnedQty <= 0
                    ) {

                        throw new \RuntimeException(
                            'Returned quantity must be greater than zero.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Lock Reseller Consignment Stock
                    |--------------------------------------------------------------------------
                    */

                    $productStock =
                        ProductStock::query()
                            ->where(
                                'company_id',
                                $consignmentReturn->company_id
                            )
                            ->where(
                                'branch_id',
                                $consignmentReturn->branch_id
                            )
                            ->where(
                                'warehouse_id',
                                $consignmentReturn->warehouse_id
                            )
                            ->where(
                                'reseller_id',
                                $consignmentReturn->reseller_id
                            )
                            ->where(
                                'product_variant_id',
                                $detail->product_variant_id
                            )
                            ->where(
                                'unit_id',
                                $detail->unit_id
                            )
                            ->lockForUpdate()
                            ->first();


                    if (! $productStock) {

                        throw new \RuntimeException(
                            'Consignment stock not found for product variant ' .
                            $detail->product_variant_id .
                            '.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Available Stock
                    |--------------------------------------------------------------------------
                    */

                    $availableQty =
                        (float)
                        $productStock->available_qty;


                    if (
                        $returnedQty >
                        $availableQty
                    ) {

                        throw new \RuntimeException(
                            'Return quantity exceeds available consignment stock.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Unit Cost
                    |--------------------------------------------------------------------------
                    */

                    $unitCost =
                        (float)
                        $productStock->average_cost;


                    if (
                        $unitCost < 0
                    ) {

                        throw new \RuntimeException(
                            'Consignment stock average cost cannot be negative.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Total Cost
                    |--------------------------------------------------------------------------
                    */

                    $totalCost =
                        round(
                            $returnedQty *
                            $unitCost,
                            2
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Update Stored Cost Snapshot
                    |--------------------------------------------------------------------------
                    */

                    $detail->update([

                        'unit_cost' =>
                            $unitCost,

                        'total_cost' =>
                            $totalCost,

                    ]);


                    $inventoryAmount +=
                        $totalCost;


                    /*
                    |--------------------------------------------------------------------------
                    | Stock Out From Reseller
                    |--------------------------------------------------------------------------
                    */

                    $this->inventoryService
                        ->stockOut([

                            'company_id' =>
                                $consignmentReturn->company_id,

                            'branch_id' =>
                                $consignmentReturn->branch_id,

                            'warehouse_id' =>
                                $consignmentReturn->warehouse_id,

                            'reseller_id' =>
                                $consignmentReturn->reseller_id,

                            'product_variant_id' =>
                                $detail->product_variant_id,

                            'unit_id' =>
                                $detail->unit_id,

                            'qty' =>
                                $returnedQty,

                            'unit_cost' =>
                                $unitCost,

                            'total_cost' =>
                                $totalCost,

                            'transaction_date' =>
                                $consignmentReturn->return_date,

                            'reference_type' =>
                                'CONSIGNMENT_RETURN',

                            'reference_id' =>
                                $consignmentReturn->id,

                            'reference_number' =>
                                $consignmentReturn->return_number,

                            'description' =>
                                'Consignment return from reseller ' .
                                $consignmentReturn->return_number,

                            'updated_by' =>
                                auth()->id(),

                        ]);


                    /*
                    |--------------------------------------------------------------------------
                    | Stock In To Warehouse
                    |--------------------------------------------------------------------------
                    */

                    $this->inventoryService
                        ->stockIn([

                            'company_id' =>
                                $consignmentReturn->company_id,

                            'branch_id' =>
                                $consignmentReturn->branch_id,

                            'warehouse_id' =>
                                $consignmentReturn->warehouse_id,

                            'reseller_id' =>
                                null,

                            'product_variant_id' =>
                                $detail->product_variant_id,

                            'unit_id' =>
                                $detail->unit_id,

                            'qty' =>
                                $returnedQty,

                            'unit_cost' =>
                                $unitCost,

                            'total_cost' =>
                                $totalCost,

                            'transaction_date' =>
                                $consignmentReturn->return_date,

                            'reference_type' =>
                                'CONSIGNMENT_RETURN',

                            'reference_id' =>
                                $consignmentReturn->id,

                            'reference_number' =>
                                $consignmentReturn->return_number,

                            'description' =>
                                'Consignment return received into warehouse ' .
                                $consignmentReturn->return_number,

                            'updated_by' =>
                                auth()->id(),

                        ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Normalize Amount
                |--------------------------------------------------------------------------
                */

                $inventoryAmount =
                    round(
                        $inventoryAmount,
                        2
                    );


                if (
                    $inventoryAmount <= 0
                ) {

                    throw new \RuntimeException(
                        'Consignment return inventory value must be greater than zero.'
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
                | Merchandise Inventory
                |--------------------------------------------------------------------------
                */

                $lines[] = [

                    'account_id' =>
                        $inventoryMerchandiseAccount->id,

                    'debit' =>
                        $inventoryAmount,

                    'credit' =>
                        0,

                    'description' =>
                        'Inventory received from consignment return ' .
                        $consignmentReturn->return_number,

                ];


                /*
                |--------------------------------------------------------------------------
                | Consignment Inventory
                |--------------------------------------------------------------------------
                */

                $lines[] = [

                    'account_id' =>
                        $inventoryConsignmentAccount->id,

                    'debit' =>
                        0,

                    'credit' =>
                        $inventoryAmount,

                    'description' =>
                        'Consignment inventory reduction for return ' .
                        $consignmentReturn->return_number,

                ];


                /*
                |--------------------------------------------------------------------------
                | Validate Journal Lines
                |--------------------------------------------------------------------------
                */

                if (
                    empty($lines)
                ) {

                    throw new \RuntimeException(
                        'Consignment return journal has no accounting lines.'
                    );

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
                                $consignmentReturn->company_id,

                            'branch_id' =>
                                $consignmentReturn->branch_id,

                            'accounting_journal_id' =>
                                $journal->id,

                            'fiscal_year_id' =>
                                $fiscalYear->id,

                            'accounting_period_id' =>
                                $accountingPeriod->id,

                            'entry_date' =>
                                $consignmentReturn->return_date,

                            'reference_type' =>
                                'CONSIGNMENT_RETURN',

                            'reference_id' =>
                                $consignmentReturn->id,

                            'reference_number' =>
                                $consignmentReturn->return_number,

                            'description' =>
                                'Consignment return ' .
                                $consignmentReturn->return_number,

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
                | Mark Posted
                |--------------------------------------------------------------------------
                */

                $consignmentReturn->update([

                    'status' =>
                        'Posted',

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

                        $consignmentReturn,

                        'POSTED',

                        'Approved',

                        'Posted',

                        'Consignment return posted.'

                    );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        ConsignmentReturnHeader $consignmentReturn,
        string $reason
    ): void {

        DB::transaction(
            function () use (
                $consignmentReturn,
                $reason
            ) {

                $consignmentReturn =
                    ConsignmentReturnHeader::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $consignmentReturn->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $consignmentReturn->status,
                        [
                            'Draft',
                            'Submitted',
                            'Approved',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Only Draft, Submitted, or Approved consignment return can be cancelled.'
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

                    throw new \RuntimeException(
                        'Cancellation reason is required.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Cancel
                |--------------------------------------------------------------------------
                */

                $previousStatus =
                    $consignmentReturn->status;


                $consignmentReturn->update([

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

                        $consignmentReturn,

                        'CANCELLED',

                        $previousStatus,

                        'Cancelled',

                        $reason

                    );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function deleteConsignmentReturns(
        array $ids
    ): void {

        DB::transaction(
            function () use ($ids) {

                $consignmentReturns =
                    ConsignmentReturnHeader::query()
                        ->whereIn(
                            'id',
                            $ids
                        )
                        ->lockForUpdate()
                        ->get();


                foreach (
                    $consignmentReturns
                    as $consignmentReturn
                ) {

                    if (
                        ! in_array(
                            $consignmentReturn->status,
                            [
                                'Draft',
                                'Rejected',
                            ],
                            true
                        )
                    ) {

                        throw new \RuntimeException(
                            'Only Draft or Rejected consignment return can be deleted.'
                        );

                    }


                    $consignmentReturn->delete();

                }

            }
        );

    }

}