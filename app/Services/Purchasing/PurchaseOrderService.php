<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\PurchaseOrderHeader;
use App\Models\Purchasing\PurchaseOrderDetail;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected DocumentActivityService $documentActivityService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService,
        DocumentActivityService $documentActivityService
    ) {
        $this->codeGeneratorService =
            $codeGeneratorService;

        $this->documentActivityService =
            $documentActivityService;
    }


  /*
|--------------------------------------------------------------------------
| Create
|--------------------------------------------------------------------------
*/

public function createPurchaseOrder(
    array $data
): PurchaseOrderHeader {

    return DB::transaction(
        function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Purchase Request
            |--------------------------------------------------------------------------
            */

            $purchaseRequest = null;

            if (
                ! empty(
                    $data['purchase_request_id']
                )
            ) {

                $purchaseRequest =
                    \App\Models\Purchasing\PurchaseRequestHeader::query()
                        ->with('details')
                        ->lockForUpdate()
                        ->findOrFail(
                            $data['purchase_request_id']
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
                    'Purchase order must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Calculate Totals
            |--------------------------------------------------------------------------
            */

            $subtotal =
                0;

            $discountAmount =
                0;

            $taxAmount =
                0;

            $grandTotal =
                0;

            $totalQuantity =
                0;


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

                $purchaseRequestDetail =
                    null;


                /*
                |--------------------------------------------------------------------------
                | Purchase Request Detail
                |--------------------------------------------------------------------------
                */

                if (
                    $purchaseRequest
                ) {

                    if (
                        empty(
                            $detail[
                                'purchase_request_detail_id'
                            ]
                        )
                    ) {

                        throw new \RuntimeException(
                            'Purchase request detail is required when a purchase request is selected.'
                        );

                    }


                    $purchaseRequestDetail =
                        $purchaseRequest
                            ->details
                            ->firstWhere(
                                'id',
                                $detail[
                                    'purchase_request_detail_id'
                                ]
                            );


                    if (
                        ! $purchaseRequestDetail
                    ) {

                        throw new \RuntimeException(
                            'Selected purchase request detail does not belong to the selected purchase request.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Product / Unit Must Match PR
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int) $detail[
                            'product_variant_id'
                        ]
                        !==
                        (int) $purchaseRequestDetail
                            ->product_variant_id
                    ) {

                        throw new \RuntimeException(
                            'Product variant does not match the selected purchase request detail.'
                        );

                    }


                    if (
                        (int) $detail[
                            'unit_id'
                        ]
                        !==
                        (int) $purchaseRequestDetail
                            ->unit_id
                    ) {

                        throw new \RuntimeException(
                            'Unit does not match the selected purchase request detail.'
                        );

                    }

                }


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
                        'Purchase order quantity must be greater than zero.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Price
                |--------------------------------------------------------------------------
                */

                $unitPrice =
                    (float) (
                        $detail['unit_price']
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Discount
                |--------------------------------------------------------------------------
                */

                $discountRate =
                    (float) (
                        $detail['discount_rate']
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Tax
                |--------------------------------------------------------------------------
                */

                $taxRate =
                    (float) (
                        $detail['tax_rate']
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Calculate Line
                |--------------------------------------------------------------------------
                */

                $lineSubtotal =
                    $qty *
                    $unitPrice;


                $lineDiscount =
                    $lineSubtotal *
                    (
                        $discountRate /
                        100
                    );


                $lineAfterDiscount =
                    $lineSubtotal -
                    $lineDiscount;


                $lineTax =
                    $lineAfterDiscount *
                    (
                        $taxRate /
                        100
                    );


                $lineTotal =
                    $lineAfterDiscount +
                    $lineTax;


                /*
                |--------------------------------------------------------------------------
                | Header Totals
                |--------------------------------------------------------------------------
                */

                $subtotal +=
                    $lineSubtotal;

                $discountAmount +=
                    $lineDiscount;

                $taxAmount +=
                    $lineTax;

                $grandTotal +=
                    $lineTotal;

                $totalQuantity +=
                    $qty;


                /*
                |--------------------------------------------------------------------------
                | Prepare Detail
                |--------------------------------------------------------------------------
                */

                $preparedDetails[] = [

                    'purchase_request_detail_id' =>
                        $purchaseRequestDetail?->id,

                    'product_variant_id' =>
                        $detail[
                            'product_variant_id'
                        ],

                    'unit_id' =>
                        $detail[
                            'unit_id'
                        ],

                    'qty' =>
                        $qty,

                    'received_qty' =>
                        0,

                    'remaining_qty' =>
                        $qty,

                    'unit_price' =>
                        $unitPrice,

                    'discount_rate' =>
                        $discountRate,

                    'discount_amount' =>
                        $lineDiscount,

                    'tax_rate' =>
                        $taxRate,

                    'tax_amount' =>
                        $lineTax,

                    'subtotal' =>
                        $lineSubtotal,

                    'total' =>
                        $lineTotal,

                    'description' =>
                        $detail[
                            'description'
                        ] ?? null,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Create Header - DRAFT
            |--------------------------------------------------------------------------
            */

            $header =
                PurchaseOrderHeader::create([

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

                    'supplier_id' =>
                        $data[
                            'supplier_id'
                        ],

                    'purchase_request_id' =>
                        $purchaseRequest?->id,

                    'number' =>
                        $this
                            ->codeGeneratorService
                            ->next(
                                'purchase_order'
                            ),

                    'order_date' =>
                        $data[
                            'order_date'
                        ],

                    'required_date' =>
                        $data[
                            'required_date'
                        ] ?? null,

                    'status' =>
                        'Draft',

                    /*
                    |--------------------------------------------------------------------------
                    | Receiving
                    |--------------------------------------------------------------------------
                    */

                    'total_quantity' =>
                        $totalQuantity,

                    'received_quantity' =>
                        0,

                    'remaining_quantity' =>
                        $totalQuantity,

                    /*
                    |--------------------------------------------------------------------------
                    | Amount
                    |--------------------------------------------------------------------------
                    */

                    'subtotal' =>
                        $subtotal,

                    'discount_amount' =>
                        $discountAmount,

                    'tax_amount' =>
                        $taxAmount,

                    'grand_total' =>
                        $grandTotal,

                    /*
                    |--------------------------------------------------------------------------
                    | Information
                    |--------------------------------------------------------------------------
                    */

                    'description' =>
                        $data[
                            'description'
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

                PurchaseOrderDetail::create(

                    array_merge(

                        [

                            'purchase_order_id' =>
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

                    'Purchase order created.'

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

    /*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

public function updatePurchaseOrder(
    PurchaseOrderHeader $purchaseOrder,
    array $data
): void {

    DB::transaction(
        function () use (
            $purchaseOrder,
            $data
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $purchaseOrder =
                PurchaseOrderHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseOrder->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                ! in_array(
                    $purchaseOrder->status,
                    [
                        'Draft',
                        'Rejected',
                    ],
                    true
                )
            ) {

                throw new \RuntimeException(
                    'Only Draft or Rejected purchase order can be edited.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Capture Old Status
            |--------------------------------------------------------------------------
            */

            $oldStatus =
                $purchaseOrder->status;


            /*
            |--------------------------------------------------------------------------
            | Purchase Request
            |--------------------------------------------------------------------------
            */

            $purchaseRequest = null;

            if (
                ! empty(
                    $data['purchase_request_id']
                )
            ) {

                $purchaseRequest =
                    \App\Models\Purchasing\PurchaseRequestHeader::query()
                        ->with('details')
                        ->lockForUpdate()
                        ->findOrFail(
                            $data['purchase_request_id']
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
                    'Purchase order must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Calculate Totals
            |--------------------------------------------------------------------------
            */

            $subtotal =
                0;

            $discountAmount =
                0;

            $taxAmount =
                0;

            $grandTotal =
                0;

            $totalQuantity =
                0;


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

                $purchaseRequestDetail =
                    null;


                /*
                |--------------------------------------------------------------------------
                | Purchase Request Detail
                |--------------------------------------------------------------------------
                */

                if (
                    $purchaseRequest
                ) {

                    if (
                        empty(
                            $detail[
                                'purchase_request_detail_id'
                            ]
                        )
                    ) {

                        throw new \RuntimeException(
                            'Purchase request detail is required when a purchase request is selected.'
                        );

                    }


                    $purchaseRequestDetail =
                        $purchaseRequest
                            ->details
                            ->firstWhere(
                                'id',
                                $detail[
                                    'purchase_request_detail_id'
                                ]
                            );


                    if (
                        ! $purchaseRequestDetail
                    ) {

                        throw new \RuntimeException(
                            'Selected purchase request detail does not belong to the selected purchase request.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Product Must Match PR
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int) $detail[
                            'product_variant_id'
                        ]
                        !==
                        (int) $purchaseRequestDetail
                            ->product_variant_id
                    ) {

                        throw new \RuntimeException(
                            'Product variant does not match the selected purchase request detail.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Unit Must Match PR
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int) $detail[
                            'unit_id'
                        ]
                        !==
                        (int) $purchaseRequestDetail
                            ->unit_id
                    ) {

                        throw new \RuntimeException(
                            'Unit does not match the selected purchase request detail.'
                        );

                    }

                }


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
                        'Purchase order quantity must be greater than zero.'
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


                /*
                |--------------------------------------------------------------------------
                | Discount
                |--------------------------------------------------------------------------
                */

                $discountRate =
                    (float) (
                        $detail['discount_rate']
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Tax
                |--------------------------------------------------------------------------
                */

                $taxRate =
                    (float) (
                        $detail['tax_rate']
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Line Calculation
                |--------------------------------------------------------------------------
                */

                $lineSubtotal =
                    $qty *
                    $unitPrice;


                $lineDiscount =
                    $lineSubtotal *
                    (
                        $discountRate /
                        100
                    );


                $lineAfterDiscount =
                    $lineSubtotal -
                    $lineDiscount;


                $lineTax =
                    $lineAfterDiscount *
                    (
                        $taxRate /
                        100
                    );


                $lineTotal =
                    $lineAfterDiscount +
                    $lineTax;


                /*
                |--------------------------------------------------------------------------
                | Header Totals
                |--------------------------------------------------------------------------
                */

                $subtotal +=
                    $lineSubtotal;

                $discountAmount +=
                    $lineDiscount;

                $taxAmount +=
                    $lineTax;

                $grandTotal +=
                    $lineTotal;

                $totalQuantity +=
                    $qty;


                /*
                |--------------------------------------------------------------------------
                | Prepare Detail
                |--------------------------------------------------------------------------
                */

                $preparedDetails[] = [

                    'purchase_request_detail_id' =>
                        $purchaseRequestDetail?->id,

                    'product_variant_id' =>
                        $detail[
                            'product_variant_id'
                        ],

                    'unit_id' =>
                        $detail[
                            'unit_id'
                        ],

                    'qty' =>
                        $qty,

                    'received_qty' =>
                        0,

                    'remaining_qty' =>
                        $qty,

                    'unit_price' =>
                        $unitPrice,

                    'discount_rate' =>
                        $discountRate,

                    'discount_amount' =>
                        $lineDiscount,

                    'tax_rate' =>
                        $taxRate,

                    'tax_amount' =>
                        $lineTax,

                    'subtotal' =>
                        $lineSubtotal,

                    'total' =>
                        $lineTotal,

                    'description' =>
                        $detail[
                            'description'
                        ] ?? null,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Update Header
            |--------------------------------------------------------------------------
            */

            $purchaseOrder->update([

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

                'supplier_id' =>
                    $data[
                        'supplier_id'
                    ],

                'purchase_request_id' =>
                    $purchaseRequest?->id,

                'order_date' =>
                    $data[
                        'order_date'
                    ],

                'required_date' =>
                    $data[
                        'required_date'
                    ] ?? null,

                'total_quantity' =>
                    $totalQuantity,

                'received_quantity' =>
                    0,

                'remaining_quantity' =>
                    $totalQuantity,

                'subtotal' =>
                    $subtotal,

                'discount_amount' =>
                    $discountAmount,

                'tax_amount' =>
                    $taxAmount,

                'grand_total' =>
                    $grandTotal,

                'description' =>
                    $data[
                        'description'
                    ] ?? null,

                'updated_by' =>
                    auth()->id(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | Replace Details
            |--------------------------------------------------------------------------
            */

            $purchaseOrder
                ->details()
                ->delete();


            foreach (
                $preparedDetails
                as $detail
            ) {

                PurchaseOrderDetail::create(

                    array_merge(

                        [

                            'purchase_order_id' =>
                                $purchaseOrder->id,

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

                $purchaseOrder->update([

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

                        $purchaseOrder,

                        'RESUBMITTED',

                        'Rejected',

                        'Draft',

                        'Rejected purchase order was corrected and resubmitted.'

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

                        $purchaseOrder,

                        'UPDATED',

                        'Draft',

                        'Draft',

                        'Purchase order updated.'

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

public function submitPurchaseOrder(
    PurchaseOrderHeader $purchaseOrder
): void {

    DB::transaction(
        function () use ($purchaseOrder) {

            $purchaseOrder =
                PurchaseOrderHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseOrder->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseOrder->status !== 'Draft'
            ) {

                throw new \RuntimeException(
                    'Only Draft purchase order can be submitted.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Details
            |--------------------------------------------------------------------------
            */

            if (
                ! $purchaseOrder
                    ->details()
                    ->exists()
            ) {

                throw new \RuntimeException(
                    'Purchase order must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Submit
            |--------------------------------------------------------------------------
            */

            $purchaseOrder->update([

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

                    $purchaseOrder,

                    'SUBMITTED',

                    'Draft',

                    'Submitted',

                    'Purchase order submitted for approval.'

                );

        }
    );
}


/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

public function approvePurchaseOrder(
    PurchaseOrderHeader $purchaseOrder
): void {

    DB::transaction(
        function () use ($purchaseOrder) {

            $purchaseOrder =
                PurchaseOrderHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseOrder->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseOrder->status !== 'Submitted'
            ) {

                throw new \RuntimeException(
                    'Only Submitted purchase order can be approved.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Approve
            |--------------------------------------------------------------------------
            */

            $purchaseOrder->update([

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

                    $purchaseOrder,

                    'APPROVED',

                    'Submitted',

                    'Approved',

                    'Purchase order approved.'

                );

        }
    );
}


/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

public function rejectPurchaseOrder(
    PurchaseOrderHeader $purchaseOrder,
    string $reason
): void {

    DB::transaction(
        function () use (
            $purchaseOrder,
            $reason
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $purchaseOrder =
                PurchaseOrderHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseOrder->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseOrder->status !== 'Submitted'
            ) {

                throw new \RuntimeException(
                    'Only Submitted purchase order can be rejected.'
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

            $purchaseOrder->update([

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

                    $purchaseOrder,

                    'REJECTED',

                    'Submitted',

                    'Rejected',

                    'Purchase order rejected.',

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
| Send to Supplier
|--------------------------------------------------------------------------
*/

public function sendPurchaseOrder(
    PurchaseOrderHeader $purchaseOrder
): void {

    DB::transaction(
        function () use ($purchaseOrder) {

            $purchaseOrder =
                PurchaseOrderHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseOrder->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseOrder->status !== 'Approved'
            ) {

                throw new \RuntimeException(
                    'Only Approved purchase order can be sent to supplier.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Send
            |--------------------------------------------------------------------------
            */

            $purchaseOrder->update([

                'status' =>
                    'Sent',

                'sent_at' =>
                    now(),

                'sent_by' =>
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

                    $purchaseOrder,

                    'SENT',

                    'Approved',

                    'Sent',

                    'Purchase order sent to supplier.'

                );

        }
    );
}


/*
|--------------------------------------------------------------------------
| Confirm by Supplier
|--------------------------------------------------------------------------
*/

public function confirmPurchaseOrder(
    PurchaseOrderHeader $purchaseOrder
): void {

    DB::transaction(
        function () use ($purchaseOrder) {

            $purchaseOrder =
                PurchaseOrderHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseOrder->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseOrder->status !== 'Sent'
            ) {

                throw new \RuntimeException(
                    'Only Sent purchase order can be confirmed.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Confirm
            |--------------------------------------------------------------------------
            */

            $purchaseOrder->update([

                'status' =>
                    'Confirmed',

                'confirmed_at' =>
                    now(),

                'confirmed_by' =>
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

                    $purchaseOrder,

                    'CONFIRMED',

                    'Sent',

                    'Confirmed',

                    'Purchase order confirmed by supplier.'

                );

        }
    );
}
/*
|--------------------------------------------------------------------------
| Duplicate
|--------------------------------------------------------------------------
*/

public function duplicatePurchaseOrder(
    PurchaseOrderHeader $purchaseOrder
): PurchaseOrderHeader {

    return DB::transaction(
        function () use ($purchaseOrder) {

            /*
            |--------------------------------------------------------------------------
            | Load Details
            |--------------------------------------------------------------------------
            */

            $purchaseOrder
                ->load('details');


            /*
            |--------------------------------------------------------------------------
            | Create Duplicate Header
            |--------------------------------------------------------------------------
            */

            $duplicate =
                PurchaseOrderHeader::create([

                    'company_id' =>
                        $purchaseOrder
                            ->company_id,

                    'branch_id' =>
                        $purchaseOrder
                            ->branch_id,

                    'warehouse_id' =>
                        $purchaseOrder
                            ->warehouse_id,

                    'supplier_id' =>
                        $purchaseOrder
                            ->supplier_id,

                    'purchase_request_id' =>
                        $purchaseOrder
                            ->purchase_request_id,

                    'number' =>
                        $this
                            ->codeGeneratorService
                            ->next(
                                'purchase_order'
                            ),

                    'order_date' =>
                        $purchaseOrder
                            ->order_date,

                    'required_date' =>
                        $purchaseOrder
                            ->required_date,

                    'status' =>
                        'Draft',

                    /*
                    |--------------------------------------------------------------------------
                    | Receiving Summary
                    |--------------------------------------------------------------------------
                    */

                    'total_quantity' =>
                        $purchaseOrder
                            ->total_quantity,

                    'received_quantity' =>
                        0,

                    'remaining_quantity' =>
                        $purchaseOrder
                            ->total_quantity,

                    /*
                    |--------------------------------------------------------------------------
                    | Amount
                    |--------------------------------------------------------------------------
                    */

                    'subtotal' =>
                        $purchaseOrder
                            ->subtotal,

                    'discount_amount' =>
                        $purchaseOrder
                            ->discount_amount,

                    'tax_amount' =>
                        $purchaseOrder
                            ->tax_amount,

                    'grand_total' =>
                        $purchaseOrder
                            ->grand_total,

                    /*
                    |--------------------------------------------------------------------------
                    | Information
                    |--------------------------------------------------------------------------
                    */

                    'description' =>
                        $purchaseOrder
                            ->description
                        ? 'Copy - ' .
                            $purchaseOrder
                                ->description
                        : 'Copy Purchase Order',

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
                $purchaseOrder->details
                as $detail
            ) {

                PurchaseOrderDetail::create([

                    'purchase_order_id' =>
                        $duplicate->id,

                    'product_variant_id' =>
                        $detail
                            ->product_variant_id,

                    'unit_id' =>
                        $detail
                            ->unit_id,

                    'qty' =>
                        $detail->qty,

                    /*
                    |--------------------------------------------------------------------------
                    | Reset Receiving
                    |--------------------------------------------------------------------------
                    */

                    'received_qty' =>
                        0,

                    'remaining_qty' =>
                        $detail->qty,

                    /*
                    |--------------------------------------------------------------------------
                    | Pricing
                    |--------------------------------------------------------------------------
                    */

                    'unit_price' =>
                        $detail
                            ->unit_price,

                    'discount_rate' =>
                        $detail
                            ->discount_rate,

                    'discount_amount' =>
                        $detail
                            ->discount_amount,

                    'tax_rate' =>
                        $detail
                            ->tax_rate,

                    'tax_amount' =>
                        $detail
                            ->tax_amount,

                    'subtotal' =>
                        $detail
                            ->subtotal,

                    'total' =>
                        $detail
                            ->total,

                    'description' =>
                        $detail
                            ->description,

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

                    'Purchase order duplicated.'

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

public function deletePurchaseOrders(
    array $ids
): void {

    DB::transaction(
        function () use ($ids) {

            $purchaseOrders =
                PurchaseOrderHeader::query()
                    ->whereIn(
                        'id',
                        $ids
                    )
                    ->lockForUpdate()
                    ->get();


            foreach (
                $purchaseOrders
                as $purchaseOrder
            ) {

                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $purchaseOrder->status,
                        [
                            'Submitted',
                            'Approved',
                            'Sent',
                            'Confirmed',
                            'Partially Received',
                            'Fully Received',
                            'Closed',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Submitted or processed purchase order cannot be deleted.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Delete Details
                |--------------------------------------------------------------------------
                */

                $purchaseOrder
                    ->details()
                    ->delete();


                /*
                |--------------------------------------------------------------------------
                | Soft Delete Header
                |--------------------------------------------------------------------------
                */

                $purchaseOrder->update([

                    'deleted_by' =>
                        auth()->id(),

                ]);

                $purchaseOrder->delete();

            }

        }
    );
}
}