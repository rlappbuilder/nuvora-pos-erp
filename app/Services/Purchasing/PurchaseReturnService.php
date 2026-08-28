<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\GoodsReceiptHeader;
use App\Models\Purchasing\PurchaseReturnHeader;
use App\Models\Purchasing\PurchaseReturnDetail;
use App\Models\Purchasing\GoodsReceiptDetail;

use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use App\Services\Inventory\InventoryService;

use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected DocumentActivityService $documentActivityService;

    protected InventoryService $inventoryService;


    public function __construct(
        CodeGeneratorService $codeGeneratorService,
        DocumentActivityService $documentActivityService,
        InventoryService $inventoryService
    ) {

        $this->codeGeneratorService =
            $codeGeneratorService;

        $this->documentActivityService =
            $documentActivityService;

        $this->inventoryService =
            $inventoryService;

    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function createPurchaseReturn(
        array $data
    ): PurchaseReturnHeader {

        return DB::transaction(
            function () use ($data) {

                /*
                |--------------------------------------------------------------------------
                | Lock Goods Receipt
                |--------------------------------------------------------------------------
                */

                $goodsReceipt =
                    GoodsReceiptHeader::query()
                        ->with('details')
                        ->lockForUpdate()
                        ->findOrFail(
                            $data['goods_receipt_id']
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Goods Receipt
                |--------------------------------------------------------------------------
                */

                if (
                    $goodsReceipt->status !== 'Posted'
                ) {

                    throw new \RuntimeException(
                        'Only Posted goods receipt can be returned.'
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
                        'Purchase return must have at least one detail.'
                    );

                }

                /*
                |--------------------------------------------------------------------------
                | Generate Number
                |--------------------------------------------------------------------------
                */

                $number =
                    $this->codeGeneratorService
                        ->next(
                            'PURCHASE_RETURN'
                        );

                /*
                |--------------------------------------------------------------------------
                | Create Header
                |--------------------------------------------------------------------------
                */

                $purchaseReturn =
                    PurchaseReturnHeader::create([

                        'company_id' =>
                            $goodsReceipt->company_id,

                        'branch_id' =>
                            $goodsReceipt->branch_id,

                        'return_number' =>
                            $number,

                        'purchase_order_id' =>
                            $goodsReceipt
                                ->purchase_order_id,

                        'goods_receipt_id' =>
                            $goodsReceipt->id,

                        'supplier_id' =>
                            $goodsReceipt
                                ->supplier_id,

                        'warehouse_id' =>
                            $goodsReceipt
                                ->warehouse_id,

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

                    /*
                    |--------------------------------------------------------------------------
                    | Lock GRN Detail
                    |--------------------------------------------------------------------------
                    */

                    $goodsReceiptDetail =
                        GoodsReceiptDetail::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $detail[
                                    'goods_receipt_detail_id'
                                ]
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | Validate GRN Ownership
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int)
                        $goodsReceiptDetail
                            ->goods_receipt_header_id
                        !==
                        (int)
                        $goodsReceipt->id
                    ) {

                        throw new \RuntimeException(
                            'Goods receipt detail does not belong to this goods receipt.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Received Quantity
                    |--------------------------------------------------------------------------
                    */

                    $receivedQty =
                        (float)
                        $goodsReceiptDetail
                            ->received_qty;


                    /*
                    |--------------------------------------------------------------------------
                    | Already Returned
                    |--------------------------------------------------------------------------
                    */

                    $alreadyReturnedQty =
                        (float)
                        PurchaseReturnDetail::query()
                            ->where(
                                'goods_receipt_detail_id',
                                $goodsReceiptDetail->id
                            )
                            ->whereHas(
                                'purchaseReturn',
                                function ($query) {

                                    $query->whereNotIn(
                                        'status',
                                        [
                                            'Cancelled',
                                            'Rejected',
                                        ]
                                    );

                                }
                            )
                            ->sum(
                                'returned_qty'
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | Remaining Return Quantity
                    |--------------------------------------------------------------------------
                    */

                    $remainingReturnQty =
                        max(
                            0,
                            $receivedQty
                            -
                            $alreadyReturnedQty
                        );


                    $returnedQty =
                        (float)
                        (
                            $detail['returned_qty']
                            ?? 0
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Return Quantity
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $returnedQty <= 0
                    ) {

                        throw new \RuntimeException(
                            'Return quantity must be greater than zero.'
                        );

                    }


                    if (
                        $returnedQty >
                        $remainingReturnQty
                    ) {

                        throw new \RuntimeException(
                            'Return quantity cannot exceed remaining returnable quantity.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Unit Cost
                    |--------------------------------------------------------------------------
                    */

                    $unitCost =
                        (float)
                        $goodsReceiptDetail
                            ->unit_cost;


                    /*
                    |--------------------------------------------------------------------------
                    | Create Detail
                    |--------------------------------------------------------------------------
                    */

                    PurchaseReturnDetail::create([

                        'purchase_return_header_id' =>
                            $purchaseReturn->id,

                        'goods_receipt_detail_id' =>
                            $goodsReceiptDetail->id,

                        'purchase_order_detail_id' =>
                            $goodsReceiptDetail
                                ->purchase_order_detail_id,

                        'product_variant_id' =>
                            $goodsReceiptDetail
                                ->product_variant_id,

                        'unit_id' =>
                            $goodsReceiptDetail
                                ->unit_id,

                        'received_qty' =>
                            $receivedQty,

                        'returned_qty' =>
                            $returnedQty,

                        'unit_cost' =>
                            $unitCost,

                        'total_cost' =>
                            $returnedQty *
                            $unitCost,

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

                        $purchaseReturn,

                        'CREATED',

                        null,

                        'Draft',

                        'Purchase return created.'

                    );


                return $purchaseReturn
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

    public function updatePurchaseReturn(
        PurchaseReturnHeader $purchaseReturn,
        array $data
    ): PurchaseReturnHeader {

        return DB::transaction(
            function () use (
                $purchaseReturn,
                $data
            ) {

                $purchaseReturn =
                    PurchaseReturnHeader::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $purchaseReturn->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $purchaseReturn->status,
                        [
                            'Draft',
                            'Rejected',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Only Draft or Rejected purchase return can be updated.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Update Header
                |--------------------------------------------------------------------------
                */

                $purchaseReturn->update([

                    'return_date' =>
                        $data['return_date']
                        ?? $purchaseReturn->return_date,

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

                $purchaseReturn
                    ->details()
                    ->delete();


                /*
                |--------------------------------------------------------------------------
                | Create Details Again
                |--------------------------------------------------------------------------
                */

                foreach (
                    $data['details']
                    ?? []
                    as $detail
                ) {

                    $goodsReceiptDetail =
                        GoodsReceiptDetail::query()
                            ->lockForUpdate()
                            ->findOrFail(
                                $detail[
                                    'goods_receipt_detail_id'
                                ]
                            );


                    /*
                    |--------------------------------------------------------------------------
                    | Validate GRN Ownership
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int)
                        $goodsReceiptDetail
                            ->goods_receipt_header_id
                        !==
                        (int)
                        $purchaseReturn
                            ->goods_receipt_id
                    ) {

                        throw new \RuntimeException(
                            'Goods receipt detail does not belong to this goods receipt.'
                        );

                    }


                    $receivedQty =
                        (float)
                        $goodsReceiptDetail
                            ->received_qty;


                    /*
                    |--------------------------------------------------------------------------
                    | Existing Returned Quantity
                    |--------------------------------------------------------------------------
                    |
                    | Exclude current Purchase Return karena detail lama
                    | sudah dihapus di atas.
                    |
                    */

                    $alreadyReturnedQty =
                        (float)
                        PurchaseReturnDetail::query()
                            ->where(
                                'goods_receipt_detail_id',
                                $goodsReceiptDetail->id
                            )
                            ->where(
                                'purchase_return_header_id',
                                '!=',
                                $purchaseReturn->id
                            )
                            ->whereHas(
                                'purchaseReturn',
                                function ($query) {

                                    $query->whereNotIn(
                                        'status',
                                        [
                                            'Cancelled',
                                            'Rejected',
                                        ]
                                    );

                                }
                            )
                            ->sum(
                                'returned_qty'
                            );


                    $remainingReturnQty =
                        max(
                            0,
                            $receivedQty
                            -
                            $alreadyReturnedQty
                        );


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


                    if (
                        $returnedQty >
                        $remainingReturnQty
                    ) {

                        throw new \RuntimeException(
                            'Return quantity cannot exceed remaining returnable quantity.'
                        );

                    }


                    $unitCost =
                        (float)
                        $goodsReceiptDetail
                            ->unit_cost;


                    PurchaseReturnDetail::create([

                        'purchase_return_header_id' =>
                            $purchaseReturn->id,

                        'goods_receipt_detail_id' =>
                            $goodsReceiptDetail->id,

                        'purchase_order_detail_id' =>
                            $goodsReceiptDetail
                                ->purchase_order_detail_id,

                        'product_variant_id' =>
                            $goodsReceiptDetail
                                ->product_variant_id,

                        'unit_id' =>
                            $goodsReceiptDetail
                                ->unit_id,

                        'received_qty' =>
                            $receivedQty,

                        'returned_qty' =>
                            $returnedQty,

                        'unit_cost' =>
                            $unitCost,

                        'total_cost' =>
                            $returnedQty *
                            $unitCost,

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
                    $purchaseReturn
                        ->details()
                        ->count()
                    <= 0
                ) {

                    throw new \RuntimeException(
                        'Purchase return must have at least one detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Document Activity
                |--------------------------------------------------------------------------
                */

                $this->documentActivityService
                    ->record(

                        $purchaseReturn,

                        'UPDATED',

                        'Rejected',

                        'Draft',

                        'Purchase return updated.'

                    );


                return $purchaseReturn
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

    public function submitPurchaseReturn(
        PurchaseReturnHeader $purchaseReturn
    ): void {

        DB::transaction(
            function () use ($purchaseReturn) {

                $purchaseReturn =
                    PurchaseReturnHeader::query()
                        ->with('details')
                        ->lockForUpdate()
                        ->findOrFail(
                            $purchaseReturn->id
                        );


                if (
                    $purchaseReturn->status !== 'Draft'
                ) {

                    throw new \RuntimeException(
                        'Only Draft purchase return can be submitted.'
                    );

                }


                if (
                    $purchaseReturn
                        ->details
                        ->isEmpty()
                ) {

                    throw new \RuntimeException(
                        'Purchase return must have at least one detail.'
                    );

                }


                $purchaseReturn->update([

                    'status' =>
                        'Submitted',

                    'updated_by' =>
                        auth()->id(),

                ]);


                $this->documentActivityService
                    ->record(

                        $purchaseReturn,

                        'SUBMITTED',

                        'Draft',

                        'Submitted',

                        'Purchase return submitted.'

                    );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */
/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

public function approvePurchaseReturn(
    PurchaseReturnHeader $purchaseReturn
): void {

    DB::transaction(
        function () use ($purchaseReturn) {

            $purchaseReturn =
                PurchaseReturnHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseReturn->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseReturn->status !== 'Submitted'
            ) {

                throw new \RuntimeException(
                    'Only Submitted purchase return can be approved.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Details
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseReturn
                    ->details()
                    ->count()
                <= 0
            ) {

                throw new \RuntimeException(
                    'Purchase return must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Approve
            |--------------------------------------------------------------------------
            */

            $purchaseReturn->update([

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
            | Document Activity
            |--------------------------------------------------------------------------
            */

            $this->documentActivityService
                ->record(

                    $purchaseReturn,

                    'APPROVED',

                    'Submitted',

                    'Approved',

                    'Purchase return approved.'

                );

        }
    );

}/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

public function reject(
    PurchaseReturnHeader $purchaseReturn,
    string $reason
): void {

    DB::transaction(
        function () use (
            $purchaseReturn,
            $reason
        ) {

            $purchaseReturn =
                PurchaseReturnHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseReturn->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseReturn->status !== 'Submitted'
            ) {

                throw new \RuntimeException(
                    'Only Submitted purchase return can be rejected.'
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
                    'Rejection reason is required.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Reject
            |--------------------------------------------------------------------------
            */

            $purchaseReturn->update([

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


            /*
            |--------------------------------------------------------------------------
            | Document Activity
            |--------------------------------------------------------------------------
            */

            $this->documentActivityService
                ->record(

                    $purchaseReturn,

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

    public function postPurchaseReturn(
        PurchaseReturnHeader $purchaseReturn
    ): void {

        DB::transaction(
            function () use ($purchaseReturn) {

                $purchaseReturn =
                    PurchaseReturnHeader::query()
                        ->with('details')
                        ->lockForUpdate()
                        ->findOrFail(
                            $purchaseReturn->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    $purchaseReturn->status !== 'Approved'
                ) {

                    throw new \RuntimeException(
                        'Only Approved purchase return can be posted.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Details
                |--------------------------------------------------------------------------
                */

                if (
                    $purchaseReturn
                        ->details
                        ->isEmpty()
                ) {

                    throw new \RuntimeException(
                        'Purchase return must have at least one detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Post Inventory
                |--------------------------------------------------------------------------
                */

                foreach (
                    $purchaseReturn->details
                    as $detail
                ) {

                    $qty =
                        (float)
                        $detail->returned_qty;


                    if (
                        $qty <= 0
                    ) {

                        throw new \RuntimeException(
                            'Returned quantity must be greater than zero.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Inventory
                    |--------------------------------------------------------------------------
                    |
                    | Purchase Return mengurangi stock.
                    |
                    */

                    $this->inventoryService
                        ->issuePurchaseReturnStock(

                            $purchaseReturn,

                            $detail

                        );

                }


                /*
                |--------------------------------------------------------------------------
                | Mark Posted
                |--------------------------------------------------------------------------
                */

                $purchaseReturn->update([

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

                $this->documentActivityService
                    ->record(

                        $purchaseReturn,

                        'POSTED',

                        'Approved',

                        'Posted',

                        'Purchase return posted.'

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
    PurchaseReturnHeader $purchaseReturn,
    string $reason
): void {

    DB::transaction(
        function () use (
            $purchaseReturn,
            $reason
        ) {

            $purchaseReturn =
                PurchaseReturnHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseReturn->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                ! in_array(
                    $purchaseReturn->status,
                    [
                        'Draft',
                        'Submitted',
                        'Approved',
                    ],
                    true
                )
            ) {

                throw new \RuntimeException(
                    'Only Draft, Submitted, or Approved purchase return can be cancelled.'
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

            $purchaseReturn->update([

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

            $this->documentActivityService
                ->record(

                    $purchaseReturn,

                    'CANCELLED',

                    null,

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

    public function deletePurchaseReturns(
        array $ids
    ): void {

        DB::transaction(
            function () use ($ids) {

                $purchaseReturns =
                    PurchaseReturnHeader::query()
                        ->whereIn(
                            'id',
                            $ids
                        )
                        ->lockForUpdate()
                        ->get();


                foreach (
                    $purchaseReturns
                    as $purchaseReturn
                ) {

                    if (
                        ! in_array(
                            $purchaseReturn->status,
                            [
                                'Draft',
                                'Rejected',
                            ],
                            true
                        )
                    ) {

                        throw new \RuntimeException(
                            'Only Draft or Rejected purchase return can be deleted.'
                        );

                    }


                    $purchaseReturn->delete();

                }

            }
        );

    }

}