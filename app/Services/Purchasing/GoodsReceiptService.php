<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\GoodsReceiptHeader;
use App\Models\Purchasing\GoodsReceiptDetail;
use App\Models\Purchasing\PurchaseOrderHeader;
use App\Models\Purchasing\PurchaseOrderDetail;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Illuminate\Support\Facades\DB;

use App\Services\Inventory\InventoryService;
class GoodsReceiptService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected DocumentActivityService $documentActivityService;

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

    public function createGoodsReceipt(
        array $data
    ): GoodsReceiptHeader {

        return DB::transaction(
            function () use ($data) {

                /*
                |--------------------------------------------------------------------------
                | Lock Purchase Order
                |--------------------------------------------------------------------------
                */

                $purchaseOrder =
                    PurchaseOrderHeader::query()
                        ->with('details')
                        ->lockForUpdate()
                        ->findOrFail(
                            $data['purchase_order_id']
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate PO Status
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $purchaseOrder->status,
                        [
                            'Confirmed',
                            'Partially Received',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Only Confirmed or Partially Received purchase order can be received.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Details
                |--------------------------------------------------------------------------
                */

                if (
                    empty($data['details'])
                ) {

                    throw new \RuntimeException(
                        'Goods receipt must have at least one detail.'
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
                    | PO Detail
                    |--------------------------------------------------------------------------
                    */

                    $purchaseOrderDetail =
                        $purchaseOrder
                            ->details
                            ->firstWhere(
                                'id',
                                $detail[
                                    'purchase_order_detail_id'
                                ]
                            );


                    if (
                        ! $purchaseOrderDetail
                    ) {

                        throw new \RuntimeException(
                            'Selected purchase order detail does not belong to the selected purchase order.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Remaining Quantity
                    |--------------------------------------------------------------------------
                    */

                    $remainingQty =
                        (float)
                        $purchaseOrderDetail
                            ->remaining_qty;


                    if (
                        $remainingQty <= 0
                    ) {

                        throw new \RuntimeException(
                            'Selected purchase order detail has no remaining quantity.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Received Quantity
                    |--------------------------------------------------------------------------
                    */

                    $receivedQty =
                        (float) (
                            $detail[
                                'received_qty'
                            ] ?? 0
                        );


                    if (
                        $receivedQty <= 0
                    ) {

                        throw new \RuntimeException(
                            'Received quantity must be greater than zero.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Prevent Over Receiving
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $receivedQty >
                        $remainingQty
                    ) {

                        throw new \RuntimeException(
                            'Received quantity cannot exceed remaining purchase order quantity.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Rejected Quantity
                    |--------------------------------------------------------------------------
                    */

                    $rejectedQty =
                        (float) (
                            $detail[
                                'rejected_qty'
                            ] ?? 0
                        );


                    if (
                        $rejectedQty < 0
                    ) {

                        throw new \RuntimeException(
                            'Rejected quantity cannot be negative.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Validate Total Physical Receipt
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (
                            $receivedQty +
                            $rejectedQty
                        )
                        >
                        $remainingQty
                    ) {

                        throw new \RuntimeException(
                            'Received quantity plus rejected quantity cannot exceed remaining purchase order quantity.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Prepare Detail
                    |--------------------------------------------------------------------------
                    */

                    $preparedDetails[] = [

                        'purchase_order_detail_id' =>
                            $purchaseOrderDetail->id,

                        'product_variant_id' =>
                            $purchaseOrderDetail
                                ->product_variant_id,

                        'unit_id' =>
                            $purchaseOrderDetail
                                ->unit_id,

                        'ordered_qty' =>
                            $remainingQty,

                        'received_qty' =>
                            $receivedQty,
                            
                        'unit_cost' =>
                            (float)
                            $purchaseOrderDetail->unit_price,

                        'rejected_qty' =>
                            $rejectedQty,

                        'remarks' =>
                            $detail[
                                'remarks'
                            ] ?? null,

                    ];

                }


                /*
                |--------------------------------------------------------------------------
                | Create Header - Draft
                |--------------------------------------------------------------------------
                */

                $header =
                    GoodsReceiptHeader::create([


                        /*
                        |--------------------------------------------------------------------------
                        | Organization
                        |--------------------------------------------------------------------------
                        */

                        'company_id' =>
                            $purchaseOrder->company_id,

                        'branch_id' =>
                            $purchaseOrder->branch_id,

                        'grn_number' =>
                            $this
                                ->codeGeneratorService
                                ->next(
                                    'purchase_receive'
                                ),

                        'purchase_order_id' =>
                            $purchaseOrder->id,

                        'supplier_id' =>
                            $purchaseOrder
                                ->supplier_id,

                        'warehouse_id' =>
                            $purchaseOrder
                                ->warehouse_id,

                        'receipt_date' =>
                            $data[
                                'receipt_date'
                            ],

                        'supplier_do_number' =>
                            $data[
                                'supplier_do_number'
                            ] ?? null,

                        'status' =>
                            'Draft',

                        'remarks' =>
                            $data[
                                'remarks'
                            ] ?? null,

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

                    GoodsReceiptDetail::create(

                        array_merge(

                            [

                                'goods_receipt_header_id' =>
                                    $header->id,

                            ],

                            $detail

                        )

                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Activity
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $header,

                        'CREATED',

                        null,

                        'Draft',

                        'Goods receipt created.'

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

    public function updateGoodsReceipt(
        GoodsReceiptHeader $goodsReceipt,
        array $data
    ): void {

        DB::transaction(
            function () use (
                $goodsReceipt,
                $data
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Header
                |--------------------------------------------------------------------------
                */

                $goodsReceipt =
                    GoodsReceiptHeader::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $goodsReceipt->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    $goodsReceipt->status !== 'Draft'
                ) {

                    throw new \RuntimeException(
                        'Only Draft goods receipt can be edited.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Lock Purchase Order
                |--------------------------------------------------------------------------
                */

                $purchaseOrder =
                    PurchaseOrderHeader::query()
                        ->with('details')
                        ->lockForUpdate()
                        ->findOrFail(
                            $data['purchase_order_id']
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate PO Status
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $purchaseOrder->status,
                        [
                            'Confirmed',
                            'Partially Received',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Selected purchase order cannot be received.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Details
                |--------------------------------------------------------------------------
                */

                if (
                    empty($data['details'])
                ) {

                    throw new \RuntimeException(
                        'Goods receipt must have at least one detail.'
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

                    $purchaseOrderDetail =
                        $purchaseOrder
                            ->details
                            ->firstWhere(
                                'id',
                                $detail[
                                    'purchase_order_detail_id'
                                ]
                            );


                    if (
                        ! $purchaseOrderDetail
                    ) {

                        throw new \RuntimeException(
                            'Selected purchase order detail does not belong to the selected purchase order.'
                        );

                    }


                    $remainingQty =
                        (float)
                        $purchaseOrderDetail
                            ->remaining_qty;


                    if (
                        $remainingQty <= 0
                    ) {

                        throw new \RuntimeException(
                            'Selected purchase order detail has no remaining quantity.'
                        );

                    }


                    $receivedQty =
                        (float) (
                            $detail[
                                'received_qty'
                            ] ?? 0
                        );


                    if (
                        $receivedQty <= 0
                    ) {

                        throw new \RuntimeException(
                            'Received quantity must be greater than zero.'
                        );

                    }


                    if (
                        $receivedQty >
                        $remainingQty
                    ) {

                        throw new \RuntimeException(
                            'Received quantity cannot exceed remaining purchase order quantity.'
                        );

                    }


                    $rejectedQty =
                        (float) (
                            $detail[
                                'rejected_qty'
                            ] ?? 0
                        );


                    if (
                        $rejectedQty < 0
                    ) {

                        throw new \RuntimeException(
                            'Rejected quantity cannot be negative.'
                        );

                    }


                    if (
                        (
                            $receivedQty +
                            $rejectedQty
                        )
                        >
                        $remainingQty
                    ) {

                        throw new \RuntimeException(
                            'Received quantity plus rejected quantity cannot exceed remaining purchase order quantity.'
                        );

                    }


                    $preparedDetails[] = [

                        'purchase_order_detail_id' =>
                            $purchaseOrderDetail->id,

                        'product_variant_id' =>
                            $purchaseOrderDetail
                                ->product_variant_id,

                        'unit_id' =>
                            $purchaseOrderDetail
                                ->unit_id,

                        'ordered_qty' =>
                            $remainingQty,

                        'received_qty' =>
                            $receivedQty,

                        'rejected_qty' =>
                            $rejectedQty,

                        'remarks' =>
                            $detail[
                                'remarks'
                            ] ?? null,

                    ];

                }


                /*
                |--------------------------------------------------------------------------
                | Update Header
                |--------------------------------------------------------------------------
                */

                $goodsReceipt->update([

                 'company_id' =>
                        $purchaseOrder->company_id,

                    'branch_id' =>
                        $purchaseOrder->branch_id,
                        
                    'purchase_order_id' =>
                        $purchaseOrder->id,

                    'supplier_id' =>
                        $purchaseOrder
                            ->supplier_id,

                    'warehouse_id' =>
                        $purchaseOrder
                            ->warehouse_id,

                    'receipt_date' =>
                        $data[
                            'receipt_date'
                        ],

                    'supplier_do_number' =>
                        $data[
                            'supplier_do_number'
                        ] ?? null,

                    'remarks' =>
                        $data[
                            'remarks'
                        ] ?? null,

                    'updated_by' =>
                        auth()->id(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Replace Details
                |--------------------------------------------------------------------------
                */

                $goodsReceipt
                    ->details()
                    ->delete();


                foreach (
                    $preparedDetails
                    as $detail
                ) {

                    GoodsReceiptDetail::create(

                        array_merge(

                            [

                                'goods_receipt_header_id' =>
                                    $goodsReceipt->id,

                            ],

                            $detail

                        )

                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Activity
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $goodsReceipt,

                        'UPDATED',

                        'Draft',

                        'Draft',

                        'Goods receipt updated.'

                    );

            }
        );
    }
/*
|--------------------------------------------------------------------------
| Post
|--------------------------------------------------------------------------
*/

public function postGoodsReceipt(
    GoodsReceiptHeader $goodsReceipt
): void {

    DB::transaction(
        function () use ($goodsReceipt) {

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
                        $goodsReceipt->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $goodsReceipt->status !== 'Approved'
            ) {

                throw new \RuntimeException(
                    'Only Approved goods receipt can be posted.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Details
            |--------------------------------------------------------------------------
            */

            if (
                $goodsReceipt->details->isEmpty()
            ) {

                throw new \RuntimeException(
                    'Goods receipt must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Lock Purchase Order Details
            |--------------------------------------------------------------------------
            */

            $purchaseOrderDetailIds =
                $goodsReceipt
                    ->details
                    ->pluck(
                        'purchase_order_detail_id'
                    )
                    ->filter()
                    ->unique()
                    ->values();


            if (
                $purchaseOrderDetailIds->isEmpty()
            ) {

                throw new \RuntimeException(
                    'Goods receipt detail must reference a purchase order detail.'
                );

            }


            $purchaseOrderDetails =
                PurchaseOrderDetail::query()
                    ->whereIn(
                        'id',
                        $purchaseOrderDetailIds
                    )
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');


            /*
            |--------------------------------------------------------------------------
            | Lock Purchase Order
            |--------------------------------------------------------------------------
            */

            $purchaseOrderIds =
                $purchaseOrderDetails
                    ->pluck(
                        'purchase_order_id'
                    )
                    ->unique()
                    ->values();


            $purchaseOrders =
                PurchaseOrderHeader::query()
                    ->whereIn(
                        'id',
                        $purchaseOrderIds
                    )
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');


            /*
            |--------------------------------------------------------------------------
            | Validate Purchase Orders
            |--------------------------------------------------------------------------
            */

            foreach (
                $purchaseOrders
                as $purchaseOrder
            ) {

                if (
                    ! in_array(
                        $purchaseOrder->status,
                        [
                            'Confirmed',
                            'Partially Received',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Purchase order is not available for receiving.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Warehouse Must Match
                |--------------------------------------------------------------------------
                */

                if (
                    (int) $purchaseOrder->warehouse_id
                    !==
                    (int) $goodsReceipt->warehouse_id
                ) {

                    throw new \RuntimeException(
                        'Goods receipt warehouse does not match purchase order warehouse.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Supplier Must Match
                |--------------------------------------------------------------------------
                */

                if (
                    (int) $purchaseOrder->supplier_id
                    !==
                    (int) $goodsReceipt->supplier_id
                ) {

                    throw new \RuntimeException(
                        'Goods receipt supplier does not match purchase order supplier.'
                    );

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Process Details
            |--------------------------------------------------------------------------
            */

            foreach (
                $goodsReceipt->details
                as $detail
            ) {

                /*
                |--------------------------------------------------------------------------
                | Purchase Order Detail
                |--------------------------------------------------------------------------
                */

                $purchaseOrderDetail =
                    $purchaseOrderDetails->get(
                        $detail->purchase_order_detail_id
                    );


                if (
                    ! $purchaseOrderDetail
                ) {

                    throw new \RuntimeException(
                        'Purchase order detail not found.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Product / Unit Validation
                |--------------------------------------------------------------------------
                */

                if (
                    (int) $detail->product_variant_id
                    !==
                    (int) $purchaseOrderDetail
                        ->product_variant_id
                ) {

                    throw new \RuntimeException(
                        'Product variant does not match purchase order detail.'
                    );

                }


                if (
                    (int) $detail->unit_id
                    !==
                    (int) $purchaseOrderDetail
                        ->unit_id
                ) {

                    throw new \RuntimeException(
                        'Unit does not match purchase order detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Quantity
                |--------------------------------------------------------------------------
                */

                $receivedQty =
                    (float) $detail->received_qty;

                $rejectedQty =
                    (float) (
                        $detail->rejected_qty
                        ?? 0
                    );


                if (
                    $receivedQty <= 0
                    &&
                    $rejectedQty <= 0
                ) {

                    throw new \RuntimeException(
                        'Goods receipt detail must have received or rejected quantity.'
                    );

                }


                if (
                    $receivedQty < 0
                ) {

                    throw new \RuntimeException(
                        'Received quantity cannot be negative.'
                    );

                }


                if (
                    $rejectedQty < 0
                ) {

                    throw new \RuntimeException(
                        'Rejected quantity cannot be negative.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | PO Remaining Quantity
                |--------------------------------------------------------------------------
                */

                $remainingQty =
                    (float)
                    $purchaseOrderDetail
                        ->remaining_qty;


                /*
                |--------------------------------------------------------------------------
                | Total Processed Quantity
                |--------------------------------------------------------------------------
                */

                $processedQty =
                    $receivedQty +
                    $rejectedQty;


                if (
                    $processedQty >
                    $remainingQty
                ) {

                    throw new \RuntimeException(
                        'Received and rejected quantity cannot exceed remaining purchase order quantity.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Unit Cost
                |--------------------------------------------------------------------------
                |
                | GRN detail tidak menyimpan cost.
                | Cost diambil dari Purchase Order Detail.
                |
                */

                $unitCost =
                    (float)
                    $purchaseOrderDetail
                        ->unit_price;


                if (
                    $unitCost < 0
                ) {

                    throw new \RuntimeException(
                        'Purchase order unit cost cannot be negative.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Stock
                |--------------------------------------------------------------------------
                |
                | Hanya received_qty yang masuk stock.
                |
                */

                          if (
                            $receivedQty > 0
                        ) {

                            $this->inventoryService
                                ->receiveGoodsReceiptStock(

                                    $goodsReceipt,

                                    $detail,

                                    $unitCost

                                );

                        }
                   /*
|--------------------------------------------------------------------------
| Update Purchase Order Detail
|--------------------------------------------------------------------------
*/

$newReceivedQty =
    (float)
    $purchaseOrderDetail
        ->received_qty
    +
    $receivedQty;


$newRemainingQty =
    max(
        0,
        (float)
        $purchaseOrderDetail
            ->remaining_qty
        -
        $processedQty
    );


$purchaseOrderDetail->update([

    'received_qty' =>
        $newReceivedQty,

    'remaining_qty' =>
        $newRemainingQty,

]);

}


/*
|--------------------------------------------------------------------------
| Update Purchase Order Headers
|--------------------------------------------------------------------------
*/

foreach (
    $purchaseOrders
    as $purchaseOrder
) {

    $purchaseOrder
        ->load('details');


    $totalQuantity =
        $purchaseOrder
            ->details
            ->sum(
                fn ($detail) =>
                    (float)
                    $detail->qty
            );


    $receivedQuantity =
        $purchaseOrder
            ->details
            ->sum(
                fn ($detail) =>
                    (float)
                    $detail->received_qty
            );


    $remainingQuantity =
        $purchaseOrder
            ->details
            ->sum(
                fn ($detail) =>
                    (float)
                    $detail->remaining_qty
            );


    /*
    |--------------------------------------------------------------------------
    | Determine Status
    |--------------------------------------------------------------------------
    */

    if (
        $remainingQuantity <= 0
    ) {

        $status =
            'Fully Received';

    } else {

        $status =
            'Partially Received';

    }


    $purchaseOrder->update([

        'total_quantity' =>
            $totalQuantity,

        'received_quantity' =>
            $receivedQuantity,

        'remaining_quantity' =>
            $remainingQuantity,

        'status' =>
            $status,

        'updated_by' =>
            auth()->id(),

    ]);

}


/*
|--------------------------------------------------------------------------
| Post Goods Receipt
|--------------------------------------------------------------------------
*/

$goodsReceipt->update([

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

                    $goodsReceipt,

                    'POSTED',

                    'Approved',

                    'Posted',

                    'Goods receipt posted.'

                );

        }
    );
}
/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

public function submitGoodsReceipt(
    GoodsReceiptHeader $goodsReceipt
): void {

    DB::transaction(
        function () use ($goodsReceipt) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $goodsReceipt =
                GoodsReceiptHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $goodsReceipt->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $goodsReceipt->status !== 'Draft'
            ) {

                throw new \RuntimeException(
                    'Only Draft goods receipt can be submitted.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Details
            |--------------------------------------------------------------------------
            */

            if (
                ! $goodsReceipt
                    ->details()
                    ->exists()
            ) {

                throw new \RuntimeException(
                    'Goods receipt must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Received Quantity
            |--------------------------------------------------------------------------
            */

            $hasReceivedQuantity =
                $goodsReceipt
                    ->details()
                    ->where(
                        'received_qty',
                        '>',
                        0
                    )
                    ->exists();


            if (
                ! $hasReceivedQuantity
            ) {

                throw new \RuntimeException(
                    'Goods receipt must have at least one received quantity.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Submit
            |--------------------------------------------------------------------------
            */

            $goodsReceipt->update([

                'status' =>
                    'Submitted',

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

                    $goodsReceipt,

                    'SUBMITTED',

                    'Draft',

                    'Submitted',

                    'Goods receipt submitted for approval.'

                );

        }
    );
}


/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

public function approveGoodsReceipt(
    GoodsReceiptHeader $goodsReceipt
): void {

    DB::transaction(
        function () use ($goodsReceipt) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $goodsReceipt =
                GoodsReceiptHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $goodsReceipt->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $goodsReceipt->status !== 'Submitted'
            ) {

                throw new \RuntimeException(
                    'Only Submitted goods receipt can be approved.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Approve
            |--------------------------------------------------------------------------
            */

            $goodsReceipt->update([

                'status' =>
                    'Approved',

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

                    $goodsReceipt,

                    'APPROVED',

                    'Submitted',

                    'Approved',

                    'Goods receipt approved.'

                );

        }
    );
}


/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

public function rejectGoodsReceipt(
    GoodsReceiptHeader $goodsReceipt,
    string $reason
): void {

    DB::transaction(
        function () use (
            $goodsReceipt,
            $reason
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $goodsReceipt =
                GoodsReceiptHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $goodsReceipt->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $goodsReceipt->status !== 'Submitted'
            ) {

                throw new \RuntimeException(
                    'Only Submitted goods receipt can be rejected.'
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

            $goodsReceipt->update([

                'status' =>
                    'Rejected',

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

                    $goodsReceipt,

                    'REJECTED',

                    'Submitted',

                    'Rejected',

                    'Goods receipt rejected.',

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
| Cancel
|--------------------------------------------------------------------------
*/

public function cancelGoodsReceipt(
    GoodsReceiptHeader $goodsReceipt,
    string $reason
): void {

    DB::transaction(
        function () use (
            $goodsReceipt,
            $reason
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $goodsReceipt =
                GoodsReceiptHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $goodsReceipt->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                ! in_array(
                    $goodsReceipt->status,
                    [
                        'Draft',
                        'Submitted',
                        'Approved',
                    ],
                    true
                )
            ) {

                throw new \RuntimeException(
                    'Goods receipt cannot be cancelled in its current status.'
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
                $goodsReceipt->status;


            /*
            |--------------------------------------------------------------------------
            | Cancel
            |--------------------------------------------------------------------------
            */

            $goodsReceipt->update([

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

                    $goodsReceipt,

                    'CANCELLED',

                    $oldStatus,

                    'Cancelled',

                    'Goods receipt cancelled.',

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
| Delete
|--------------------------------------------------------------------------
*/

public function deleteGoodsReceipts(
    array $ids
): void {

    DB::transaction(
        function () use ($ids) {

            $goodsReceipts =
                GoodsReceiptHeader::query()
                    ->whereIn(
                        'id',
                        $ids
                    )
                    ->lockForUpdate()
                    ->get();


            foreach (
                $goodsReceipts
                as $goodsReceipt
            ) {

                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $goodsReceipt->status,
                        [
                            'Submitted',
                            'Approved',
                            'Posted',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Submitted, approved, or posted goods receipt cannot be deleted.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Delete Details
                |--------------------------------------------------------------------------
                */

                $goodsReceipt
                    ->details()
                    ->delete();


                /*
                |--------------------------------------------------------------------------
                | Audit
                |--------------------------------------------------------------------------
                */

                $goodsReceipt->update([

                    'updated_by' =>
                        auth()->id(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Soft Delete Header
                |--------------------------------------------------------------------------
                */

                $goodsReceipt->delete();

            }

        }
    );
}
}