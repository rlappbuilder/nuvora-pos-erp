<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\PurchaseInvoiceHeader;
use App\Models\Purchasing\PurchaseInvoiceDetail;
use App\Models\Purchasing\PurchaseOrderHeader;
use App\Models\Purchasing\GoodsReceiptHeader;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Illuminate\Support\Facades\DB;
use App\Services\Accounting\AccountMappingService;
use App\Services\Accounting\JournalEntryService;
use App\Services\Inventory\InventoryService;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\AccountingPeriod;
use App\Models\Accounting\AccountingJournal;
use App\Models\Inventory\InventoryMovement;
class PurchaseInvoiceService
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

public function createPurchaseInvoice(
    array $data
): PurchaseInvoiceHeader {

    return DB::transaction(
        function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Purchase Order
            |--------------------------------------------------------------------------
            */

            $purchaseOrder =
                PurchaseOrderHeader::query()
                    ->with('details')
                    ->lockForUpdate()
                    ->findOrFail(
                        $data[
                            'purchase_order_id'
                        ]
                    );


            /*
            |--------------------------------------------------------------------------
            | Goods Receipt
            |--------------------------------------------------------------------------
            */

            $goodsReceipt =
                GoodsReceiptHeader::query()
                    ->with('details')
                    ->lockForUpdate()
                    ->findOrFail(
                        $data[
                            'goods_receipt_id'
                        ]
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Purchase Order / Goods Receipt
            |--------------------------------------------------------------------------
            */

            if (
                (int)
                $goodsReceipt->purchase_order_id
                !==
                (int)
                $purchaseOrder->id
            ) {

                throw new \RuntimeException(
                    'Goods receipt does not belong to the selected purchase order.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Goods Receipt Status
            |--------------------------------------------------------------------------
            */

            if (
                $goodsReceipt->status !== 'Posted'
            ) {

                throw new \RuntimeException(
                    'Only Posted goods receipt can be invoiced.'
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
                    'Purchase invoice must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Supplier Invoice Number
            |--------------------------------------------------------------------------
            */

            if (
                empty(
                    trim(
                        $data['invoice_number']
                        ?? ''
                    )
                )
            ) {

                throw new \RuntimeException(
                    'Supplier invoice number is required.'
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
                | Purchase Order Detail
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
                | Goods Receipt Detail
                |--------------------------------------------------------------------------
                */

                $goodsReceiptDetail =
                    $goodsReceipt
                        ->details
                        ->firstWhere(
                            'id',
                            $detail[
                                'goods_receipt_detail_id'
                            ]
                        );


                if (
                    ! $goodsReceiptDetail
                ) {

                    throw new \RuntimeException(
                        'Selected goods receipt detail does not belong to the selected goods receipt.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | 3-Way Matching
                |--------------------------------------------------------------------------
                */

                if (
                    (int)
                    $goodsReceiptDetail
                        ->purchase_order_detail_id
                    !==
                    (int)
                    $purchaseOrderDetail->id
                ) {

                    throw new \RuntimeException(
                        'Goods receipt detail does not match the selected purchase order detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Product Variant
                |--------------------------------------------------------------------------
                */

                if (
                    (int)
                    $detail[
                        'product_variant_id'
                    ]
                    !==
                    (int)
                    $purchaseOrderDetail
                        ->product_variant_id
                ) {

                    throw new \RuntimeException(
                        'Product variant does not match the purchase order detail.'
                    );

                }


                if (
                    (int)
                    $detail[
                        'product_variant_id'
                    ]
                    !==
                    (int)
                    $goodsReceiptDetail
                        ->product_variant_id
                ) {

                    throw new \RuntimeException(
                        'Product variant does not match the goods receipt detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Unit
                |--------------------------------------------------------------------------
                */

                if (
                    (int)
                    $detail[
                        'unit_id'
                    ]
                    !==
                    (int)
                    $purchaseOrderDetail
                        ->unit_id
                ) {

                    throw new \RuntimeException(
                        'Unit does not match the purchase order detail.'
                    );

                }


                if (
                    (int)
                    $detail[
                        'unit_id'
                    ]
                    !==
                    (int)
                    $goodsReceiptDetail
                        ->unit_id
                ) {

                    throw new \RuntimeException(
                        'Unit does not match the goods receipt detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Invoice Quantity
                |--------------------------------------------------------------------------
                */

                $invoicedQty =
                    (float) (
                        $detail[
                            'invoiced_qty'
                        ]
                        ?? 0
                    );


                if (
                    $invoicedQty <= 0
                ) {

                    throw new \RuntimeException(
                        'Invoice quantity must be greater than zero.'
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


                if (
                    $invoicedQty >
                    $receivedQty
                ) {

                    throw new \RuntimeException(
                        'Invoice quantity cannot exceed received quantity.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Unit Price
                |--------------------------------------------------------------------------
                */

                $unitPrice =
                    (float) (
                        $detail[
                            'unit_price'
                        ]
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Discount
                |--------------------------------------------------------------------------
                */

                $lineDiscount =
                    (float) (
                        $detail[
                            'discount_amount'
                        ]
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Tax
                |--------------------------------------------------------------------------
                */

                $lineTax =
                    (float) (
                        $detail[
                            'tax_amount'
                        ]
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Calculate Line
                |--------------------------------------------------------------------------
                */

                $lineSubtotal =
                    $invoicedQty *
                    $unitPrice;


                $lineAfterDiscount =
                    $lineSubtotal -
                    $lineDiscount;


                $lineTotal =
                    $lineAfterDiscount +
                    $lineTax;


                /*
                |--------------------------------------------------------------------------
                | Validate Discount
                |--------------------------------------------------------------------------
                */

                if (
                    $lineDiscount >
                    $lineSubtotal
                ) {

                    throw new \RuntimeException(
                        'Discount amount cannot exceed line subtotal.'
                    );

                }


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


                /*
                |--------------------------------------------------------------------------
                | Prepare Detail
                |--------------------------------------------------------------------------
                */

                $preparedDetails[] = [

                    'purchase_order_detail_id' =>
                        $purchaseOrderDetail->id,

                    'goods_receipt_detail_id' =>
                        $goodsReceiptDetail->id,

                    'product_variant_id' =>
                        $detail[
                            'product_variant_id'
                        ],

                    'unit_id' =>
                        $detail[
                            'unit_id'
                        ],

                    'ordered_qty' =>
                        (float)
                        $purchaseOrderDetail
                            ->qty,

                    'received_qty' =>
                        $receivedQty,

                    'invoiced_qty' =>
                        $invoicedQty,

                    'unit_price' =>
                        $unitPrice,

                    'discount_amount' =>
                        $lineDiscount,

                    'tax_amount' =>
                        $lineTax,

                    'subtotal' =>
                        $lineSubtotal,

                    'total_amount' =>
                        $lineTotal,

                    'remarks' =>
                        $detail[
                            'remarks'
                        ] ?? null,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Create Header - DRAFT
            |--------------------------------------------------------------------------
            */

            $header =
                PurchaseInvoiceHeader::create([

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

                    /*
                    |--------------------------------------------------------------------------
                    | Document
                    |--------------------------------------------------------------------------
                    */

                    'number' =>
                        $this
                            ->codeGeneratorService
                            ->next(
                                'purchase_invoice'
                            ),

                    'invoice_number' =>
                        trim(
                            $data[
                                'invoice_number'
                            ]
                        ),

                    /*
                    |--------------------------------------------------------------------------
                    | References
                    |--------------------------------------------------------------------------
                    */

                    'purchase_order_id' =>
                        $purchaseOrder->id,

                    'goods_receipt_id' =>
                        $goodsReceipt->id,

                    /*
                    |--------------------------------------------------------------------------
                    | Master Data
                    |--------------------------------------------------------------------------
                    */

                    'supplier_id' =>
                        $data[
                            'supplier_id'
                        ],

                    'warehouse_id' =>
                        $data[
                            'warehouse_id'
                        ],

                    'payment_term_id' =>
                        $data[
                            'payment_term_id'
                        ],

                    'currency_id' =>
                        $data[
                            'currency_id'
                        ],

                    'tax_id' =>
                        $data[
                            'tax_id'
                        ] ?? null,

                    /*
                    |--------------------------------------------------------------------------
                    | Invoice Information
                    |--------------------------------------------------------------------------
                    */

                    'invoice_date' =>
                        $data[
                            'invoice_date'
                        ],

                    'due_date' =>
                        $data[
                            'due_date'
                        ],

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

                    'paid_amount' =>
                        0,

                    'outstanding_amount' =>
                        $grandTotal,

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

                PurchaseInvoiceDetail::create(

                    array_merge(

                        [

                            'purchase_invoice_header_id' =>
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

                    'Purchase invoice created.'

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

public function updatePurchaseInvoice(
    PurchaseInvoiceHeader $purchaseInvoice,
    array $data
): void {

    DB::transaction(
        function () use (
            $purchaseInvoice,
            $data
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $purchaseInvoice =
                PurchaseInvoiceHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseInvoice->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                ! in_array(
                    $purchaseInvoice->status,
                    [
                        'Draft',
                        'Rejected',
                    ],
                    true
                )
            ) {

                throw new \RuntimeException(
                    'Only Draft or Rejected purchase invoice can be edited.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Capture Old Status
            |--------------------------------------------------------------------------
            */

            $oldStatus =
                $purchaseInvoice->status;


            /*
            |--------------------------------------------------------------------------
            | Purchase Order
            |--------------------------------------------------------------------------
            */

            $purchaseOrder =
                PurchaseOrderHeader::query()
                    ->with('details')
                    ->lockForUpdate()
                    ->findOrFail(
                        $data[
                            'purchase_order_id'
                        ]
                    );


            /*
            |--------------------------------------------------------------------------
            | Goods Receipt
            |--------------------------------------------------------------------------
            */

            $goodsReceipt =
                GoodsReceiptHeader::query()
                    ->with('details')
                    ->lockForUpdate()
                    ->findOrFail(
                        $data[
                            'goods_receipt_id'
                        ]
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Purchase Order / Goods Receipt
            |--------------------------------------------------------------------------
            */

            if (
                (int)
                $goodsReceipt->purchase_order_id
                !==
                (int)
                $purchaseOrder->id
            ) {

                throw new \RuntimeException(
                    'Goods receipt does not belong to the selected purchase order.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Goods Receipt Status
            |--------------------------------------------------------------------------
            */

            if (
                $goodsReceipt->status !== 'Posted'
            ) {

                throw new \RuntimeException(
                    'Only Posted goods receipt can be invoiced.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Supplier Invoice Number
            |--------------------------------------------------------------------------
            */

            if (
                empty(
                    trim(
                        $data['invoice_number']
                        ?? ''
                    )
                )
            ) {

                throw new \RuntimeException(
                    'Supplier invoice number is required.'
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
                    'Purchase invoice must have at least one detail.'
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
                | Purchase Order Detail
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
                | Goods Receipt Detail
                |--------------------------------------------------------------------------
                */

                $goodsReceiptDetail =
                    $goodsReceipt
                        ->details
                        ->firstWhere(
                            'id',
                            $detail[
                                'goods_receipt_detail_id'
                            ]
                        );


                if (
                    ! $goodsReceiptDetail
                ) {

                    throw new \RuntimeException(
                        'Selected goods receipt detail does not belong to the selected goods receipt.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | 3-Way Matching
                |--------------------------------------------------------------------------
                */

                if (
                    (int)
                    $goodsReceiptDetail
                        ->purchase_order_detail_id
                    !==
                    (int)
                    $purchaseOrderDetail->id
                ) {

                    throw new \RuntimeException(
                        'Goods receipt detail does not match the selected purchase order detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Product Variant
                |--------------------------------------------------------------------------
                */

                if (
                    (int)
                    $detail[
                        'product_variant_id'
                    ]
                    !==
                    (int)
                    $purchaseOrderDetail
                        ->product_variant_id
                ) {

                    throw new \RuntimeException(
                        'Product variant does not match the purchase order detail.'
                    );

                }


                if (
                    (int)
                    $detail[
                        'product_variant_id'
                    ]
                    !==
                    (int)
                    $goodsReceiptDetail
                        ->product_variant_id
                ) {

                    throw new \RuntimeException(
                        'Product variant does not match the goods receipt detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Unit
                |--------------------------------------------------------------------------
                */

                if (
                    (int)
                    $detail[
                        'unit_id'
                    ]
                    !==
                    (int)
                    $purchaseOrderDetail
                        ->unit_id
                ) {

                    throw new \RuntimeException(
                        'Unit does not match the purchase order detail.'
                    );

                }


                if (
                    (int)
                    $detail[
                        'unit_id'
                    ]
                    !==
                    (int)
                    $goodsReceiptDetail
                        ->unit_id
                ) {

                    throw new \RuntimeException(
                        'Unit does not match the goods receipt detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Invoice Quantity
                |--------------------------------------------------------------------------
                */

                $invoicedQty =
                    (float) (
                        $detail[
                            'invoiced_qty'
                        ]
                        ?? 0
                    );


                if (
                    $invoicedQty <= 0
                ) {

                    throw new \RuntimeException(
                        'Invoice quantity must be greater than zero.'
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


                if (
                    $invoicedQty >
                    $receivedQty
                ) {

                    throw new \RuntimeException(
                        'Invoice quantity cannot exceed received quantity.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Unit Price
                |--------------------------------------------------------------------------
                */

                $unitPrice =
                    (float) (
                        $detail[
                            'unit_price'
                        ]
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Discount
                |--------------------------------------------------------------------------
                */

                $lineDiscount =
                    (float) (
                        $detail[
                            'discount_amount'
                        ]
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Tax
                |--------------------------------------------------------------------------

                */

                $lineTax =
                    (float) (
                        $detail[
                            'tax_amount'
                        ]
                        ?? 0
                    );


                /*
                |--------------------------------------------------------------------------
                | Calculate Line
                |--------------------------------------------------------------------------
                */

                $lineSubtotal =
                    $invoicedQty *
                    $unitPrice;


                $lineAfterDiscount =
                    $lineSubtotal -
                    $lineDiscount;


                $lineTotal =
                    $lineAfterDiscount +
                    $lineTax;


                /*
                |--------------------------------------------------------------------------
                | Validate Discount
                |--------------------------------------------------------------------------
                */

                if (
                    $lineDiscount >
                    $lineSubtotal
                ) {

                    throw new \RuntimeException(
                        'Discount amount cannot exceed line subtotal.'
                    );

                }


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


                /*
                |--------------------------------------------------------------------------
                | Prepare Detail
                |--------------------------------------------------------------------------
                */

                $preparedDetails[] = [

                    'purchase_order_detail_id' =>
                        $purchaseOrderDetail->id,

                    'goods_receipt_detail_id' =>
                        $goodsReceiptDetail->id,

                    'product_variant_id' =>
                        $detail[
                            'product_variant_id'
                        ],

                    'unit_id' =>
                        $detail[
                            'unit_id'
                        ],

                    'ordered_qty' =>
                        (float)
                        $purchaseOrderDetail
                            ->qty,

                    'received_qty' =>
                        $receivedQty,

                    'invoiced_qty' =>
                        $invoicedQty,

                    'unit_price' =>
                        $unitPrice,

                    'discount_amount' =>
                        $lineDiscount,

                    'tax_amount' =>
                        $lineTax,

                    'subtotal' =>
                        $lineSubtotal,

                    'total_amount' =>
                        $lineTotal,

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

            $purchaseInvoice->update([

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

                /*
                |--------------------------------------------------------------------------
                | Supplier Invoice
                |--------------------------------------------------------------------------
                */

                'invoice_number' =>
                    trim(
                        $data[
                            'invoice_number'
                        ]
                    ),

                /*
                |--------------------------------------------------------------------------
                | References
                |--------------------------------------------------------------------------
                */

                'purchase_order_id' =>
                    $purchaseOrder->id,

                'goods_receipt_id' =>
                    $goodsReceipt->id,

                /*
                |--------------------------------------------------------------------------
                | Master Data
                |--------------------------------------------------------------------------
                */

                'supplier_id' =>
                    $data[
                        'supplier_id'
                    ],

                'warehouse_id' =>
                    $data[
                        'warehouse_id'
                    ],

                'payment_term_id' =>
                    $data[
                        'payment_term_id'
                    ],

                'currency_id' =>
                    $data[
                        'currency_id'
                    ],

                'tax_id' =>
                    $data[
                        'tax_id'
                    ] ?? null,

                /*
                |--------------------------------------------------------------------------
                | Invoice Information
                |--------------------------------------------------------------------------
                */

                'invoice_date' =>
                    $data[
                        'invoice_date'
                    ],

                'due_date' =>
                    $data[
                        'due_date'
                    ],

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

                'paid_amount' =>
                    0,

                'outstanding_amount' =>
                    $grandTotal,

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

            $purchaseInvoice
                ->details()
                ->delete();


            foreach (
                $preparedDetails
                as $detail
            ) {

                PurchaseInvoiceDetail::create(

                    array_merge(

                        [

                            'purchase_invoice_header_id' =>
                                $purchaseInvoice->id,

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

                $purchaseInvoice->update([

                    'status' =>
                        'Draft',

                    'rejected_at' =>
                        null,

                    'rejected_by' =>
                        null,

                    'reject_reason' =>
                        null,

                    'updated_by' =>
                        auth()->id(),

                ]);


                $this
                    ->documentActivityService
                    ->record(

                        $purchaseInvoice,

                        'RESUBMITTED',

                        'Rejected',

                        'Draft',

                        'Rejected purchase invoice was corrected and resubmitted.'

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

                        $purchaseInvoice,

                        'UPDATED',

                        'Draft',

                        'Draft',

                        'Purchase invoice updated.'

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

public function submitPurchaseInvoice(
    PurchaseInvoiceHeader $purchaseInvoice
): void {

    DB::transaction(
        function () use ($purchaseInvoice) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $purchaseInvoice =
                PurchaseInvoiceHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseInvoice->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseInvoice->status !== 'Draft'
            ) {

                throw new \RuntimeException(
                    'Only Draft purchase invoice can be submitted.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Details
            |--------------------------------------------------------------------------
            */

            if (
                ! $purchaseInvoice
                    ->details()
                    ->exists()
            ) {

                throw new \RuntimeException(
                    'Purchase invoice must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Submit
            |--------------------------------------------------------------------------
            */

            $purchaseInvoice->update([

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

                    $purchaseInvoice,

                    'SUBMITTED',

                    'Draft',

                    'Submitted',

                    'Purchase invoice submitted for approval.'

                );

        }
    );
}
/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

public function approvePurchaseInvoice(
    PurchaseInvoiceHeader $purchaseInvoice
): void {

    DB::transaction(
        function () use ($purchaseInvoice) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $purchaseInvoice =
                PurchaseInvoiceHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseInvoice->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseInvoice->status !== 'Submitted'
            ) {

                throw new \RuntimeException(
                    'Only Submitted purchase invoice can be approved.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Approve
            |--------------------------------------------------------------------------
            */

            $purchaseInvoice->update([

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

                    $purchaseInvoice,

                    'APPROVED',

                    'Submitted',

                    'Approved',

                    'Purchase invoice approved.'

                );

        }
    );
}
/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

public function rejectPurchaseInvoice(
    PurchaseInvoiceHeader $purchaseInvoice,
    string $reason
): void {

    DB::transaction(
        function () use (
            $purchaseInvoice,
            $reason
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $purchaseInvoice =
                PurchaseInvoiceHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseInvoice->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseInvoice->status !== 'Submitted'
            ) {

                throw new \RuntimeException(
                    'Only Submitted purchase invoice can be rejected.'
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

            $purchaseInvoice->update([

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
            | Activity
            |--------------------------------------------------------------------------
            */

            $this
                ->documentActivityService
                ->record(

                    $purchaseInvoice,

                    'REJECTED',

                    'Submitted',

                    'Rejected',

                    'Purchase invoice rejected.',

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

public function postPurchaseInvoice(
    PurchaseInvoiceHeader $purchaseInvoice
): void {

    DB::transaction(
        function () use ($purchaseInvoice) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $purchaseInvoice =
                PurchaseInvoiceHeader::query()
                    ->with([
                        'details.goodsReceiptDetail',
                        'goodsReceipt',
                    ])
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseInvoice->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseInvoice->status !== 'Approved'
            ) {

                throw new \RuntimeException(
                    'Only Approved purchase invoice can be posted.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Details
            |--------------------------------------------------------------------------
            */

            $details =
                $purchaseInvoice
                    ->details;

            if (
                $details->isEmpty()
            ) {

                throw new \RuntimeException(
                    'Purchase invoice must have at least one detail.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Validate Goods Receipt
            |--------------------------------------------------------------------------
            */

            $goodsReceipt =
                $purchaseInvoice
                    ->goodsReceipt;

            if (! $goodsReceipt) {

                throw new \RuntimeException(
                    'Purchase invoice must be linked to a goods receipt.'
                );

            }

            if (
                $goodsReceipt->status !== 'Posted'
            ) {

                throw new \RuntimeException(
                    'Goods receipt must be posted before purchase invoice.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Resolve Accounting Period
            |--------------------------------------------------------------------------
            */

            $fiscalYear =
                FiscalYear::query()
                    ->where(
                        'company_id',
                        $purchaseInvoice->company_id
                    )
                    ->whereDate(
                        'start_date',
                        '<=',
                        $purchaseInvoice->invoice_date
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        $purchaseInvoice->invoice_date
                    )
                    ->where(
                        'status',
                        'Open'
                    )
                    ->first();

            if (! $fiscalYear) {

                throw new \RuntimeException(
                    'No open fiscal year found for purchase invoice date.'
                );

            }


            $accountingPeriod =
                AccountingPeriod::query()
                    ->where(
                        'company_id',
                        $purchaseInvoice->company_id
                    )
                    ->where(
                        'fiscal_year_id',
                        $fiscalYear->id
                    )
                    ->whereDate(
                        'start_date',
                        '<=',
                        $purchaseInvoice->invoice_date
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        $purchaseInvoice->invoice_date
                    )
                    ->where(
                        'status',
                        'Open'
                    )
                    ->first();

            if (! $accountingPeriod) {

                throw new \RuntimeException(
                    'No open accounting period found for purchase invoice date.'
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
                        $purchaseInvoice->company_id
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

            $grniAccount =
                $this->accountMappingService
                    ->getAccount(
                        $purchaseInvoice->company_id,
                        'goods_received_not_invoiced'
                    );

            $inventoryAccount =
                $this->accountMappingService
                    ->getAccount(
                        $purchaseInvoice->company_id,
                        'inventory_merchandise'
                    );

            $inputVatAccount =
                $this->accountMappingService
                    ->getAccount(
                        $purchaseInvoice->company_id,
                        'input_vat'
                    );

            $tradePayableAccount =
                $this->accountMappingService
                    ->getAccount(
                        $purchaseInvoice->company_id,
                        'trade_payable'
                    );


            /*
            |--------------------------------------------------------------------------
            | Calculate GRNI
            |--------------------------------------------------------------------------
            */

            $grniAmount = 0;

            foreach ($details as $detail) {

                $grnDetail =
                    $detail->goodsReceiptDetail;

                if (! $grnDetail) {

                    throw new \RuntimeException(
                        'Purchase invoice detail is missing goods receipt detail.'
                    );

                }

                $receivedQty =
                    (float) $grnDetail->received_qty;

                $unitCost =
                    (float) $grnDetail->unit_cost;

                $grniAmount +=
                    $receivedQty * $unitCost;

            }


            /*
            |--------------------------------------------------------------------------
            | Invoice Amounts
            |--------------------------------------------------------------------------
            */

            $subtotal =
                (float) $purchaseInvoice->subtotal;

            $discount =
                (float) $purchaseInvoice->discount_amount;

            $tax =
                (float) $purchaseInvoice->tax_amount;

            $grandTotal =
                (float) $purchaseInvoice->grand_total;

            $netPurchase =
                $subtotal - $discount;


            if ($netPurchase < 0) {

                throw new \RuntimeException(
                    'Purchase discount cannot exceed subtotal.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Inventory Adjustment
            |--------------------------------------------------------------------------
            */

            $inventoryAmount =
                $grniAmount -
                $netPurchase;


            /*
            |--------------------------------------------------------------------------
            | Validate Grand Total
            |--------------------------------------------------------------------------
            */

            $expectedGrandTotal =
                $netPurchase + $tax;

            if (
                abs(
                    $expectedGrandTotal -
                    $grandTotal
                ) > 0.01
            ) {

                throw new \RuntimeException(
                    'Purchase invoice total does not match subtotal, discount and tax.'
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
            | GRNI
            |--------------------------------------------------------------------------
            */

            if ($grniAmount > 0) {

                $lines[] = [

                    'account_id' =>
                        $grniAccount->id,

                    'debit' =>
                        round(
                            $grniAmount,
                            2
                        ),

                    'credit' =>
                        0,

                    'description' =>
                        'Clear GRNI for purchase invoice ' .
                        $purchaseInvoice->invoice_number,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Input VAT
            |--------------------------------------------------------------------------
            */

            if ($tax > 0) {

                $lines[] = [

                    'account_id' =>
                        $inputVatAccount->id,

                    'debit' =>
                        round(
                            $tax,
                            2
                        ),

                    'credit' =>
                        0,

                    'description' =>
                        'Input VAT for purchase invoice ' .
                        $purchaseInvoice->invoice_number,

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            if (
                abs($inventoryAmount) > 0.01
            ) {

                if (
                    $inventoryAmount > 0
                ) {

                    $lines[] = [

                        'account_id' =>
                            $inventoryAccount->id,

                        'debit' =>
                            0,

                        'credit' =>
                            round(
                                $inventoryAmount,
                                2
                            ),

                        'description' =>
                            'Purchase price adjustment for invoice ' .
                            $purchaseInvoice->invoice_number,

                    ];

                } else {

                    $lines[] = [

                        'account_id' =>
                            $inventoryAccount->id,

                        'debit' =>
                            round(
                                abs($inventoryAmount),
                                2
                            ),

                        'credit' =>
                            0,

                        'description' =>
                            'Purchase price adjustment for invoice ' .
                            $purchaseInvoice->invoice_number,

                    ];

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Trade Payable
            |--------------------------------------------------------------------------
            */

            if ($grandTotal > 0) {

                $lines[] = [

                    'account_id' =>
                        $tradePayableAccount->id,

                    'debit' =>
                        0,

                    'credit' =>
                        round(
                            $grandTotal,
                            2
                        ),

                    'description' =>
                        'Trade payable for purchase invoice ' .
                        $purchaseInvoice->invoice_number,

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
                            $purchaseInvoice->company_id,

                        'branch_id' =>
                            $purchaseInvoice->branch_id,

                        'accounting_journal_id' =>
                            $journal->id,

                        'fiscal_year_id' =>
                            $fiscalYear->id,

                        'accounting_period_id' =>
                            $accountingPeriod->id,

                        'entry_date' =>
                            $purchaseInvoice->invoice_date,

                        'reference_type' =>
                            'PURCHASE_INVOICE',

                        'reference_id' =>
                            $purchaseInvoice->id,

                        'reference_number' =>
                            $purchaseInvoice->invoice_number,

                        'description' =>
                            'Purchase invoice ' .
                            $purchaseInvoice->invoice_number,

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

            $purchaseInvoice->update([

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

                    $purchaseInvoice,

                    'POSTED',

                    'Approved',

                    'Posted',

                    'Purchase invoice posted.'

                );

        }
    );
}
/*
|--------------------------------------------------------------------------
| Duplicate
|--------------------------------------------------------------------------
*/

public function duplicatePurchaseInvoice(
    PurchaseInvoiceHeader $purchaseInvoice
): PurchaseInvoiceHeader {

    return DB::transaction(
        function () use ($purchaseInvoice) {

            /*
            |--------------------------------------------------------------------------
            | Load Details
            |--------------------------------------------------------------------------
            */

            $purchaseInvoice
                ->load('details');


            /*
            |--------------------------------------------------------------------------
            | Create Duplicate Header
            |--------------------------------------------------------------------------
            */

            $duplicate =
                PurchaseInvoiceHeader::create([

                    /*
                    |--------------------------------------------------------------------------
                    | Organization
                    |--------------------------------------------------------------------------
                    */

                    'company_id' =>
                        $purchaseInvoice
                            ->company_id,

                    'branch_id' =>
                        $purchaseInvoice
                            ->branch_id,

                    /*
                    |--------------------------------------------------------------------------
                    | Document References
                    |--------------------------------------------------------------------------
                    */

                    'purchase_order_id' =>
                        $purchaseInvoice
                            ->purchase_order_id,

                    'goods_receipt_id' =>
                        $purchaseInvoice
                            ->goods_receipt_id,

                    /*
                    |--------------------------------------------------------------------------
                    | Master Data
                    |--------------------------------------------------------------------------
                    */

                    'supplier_id' =>
                        $purchaseInvoice
                            ->supplier_id,

                    'warehouse_id' =>
                        $purchaseInvoice
                            ->warehouse_id,

                    'payment_term_id' =>
                        $purchaseInvoice
                            ->payment_term_id,

                    'currency_id' =>
                        $purchaseInvoice
                            ->currency_id,

                    'tax_id' =>
                        $purchaseInvoice
                            ->tax_id,

                    /*
                    |--------------------------------------------------------------------------
                    | Document
                    |--------------------------------------------------------------------------
                    */

                    'invoice_number' =>
                        $this
                            ->codeGeneratorService
                            ->next(
                                'purchase_invoice'
                            ),

                    /*
                    |--------------------------------------------------------------------------
                    | Invoice Information
                    |--------------------------------------------------------------------------
                    */

                    'invoice_date' =>
                        $purchaseInvoice
                            ->invoice_date,

                    'due_date' =>
                        $purchaseInvoice
                            ->due_date,

                    /*
                    |--------------------------------------------------------------------------
                    | Amount
                    |--------------------------------------------------------------------------
                    */

                    'subtotal' =>
                        $purchaseInvoice
                            ->subtotal,

                    'discount_amount' =>
                        $purchaseInvoice
                            ->discount_amount,

                    'tax_amount' =>
                        $purchaseInvoice
                            ->tax_amount,

                    'grand_total' =>
                        $purchaseInvoice
                            ->grand_total,

                    /*
                    |--------------------------------------------------------------------------
                    | Reset Payment
                    |--------------------------------------------------------------------------
                    */

                    'paid_amount' =>
                        0,

                    'outstanding_amount' =>
                        $purchaseInvoice
                            ->grand_total,

                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    'status' =>
                        'Draft',

                    /*
                    |--------------------------------------------------------------------------
                    | Information
                    |--------------------------------------------------------------------------
                    */

                    'remarks' =>
                        $purchaseInvoice
                            ->remarks
                        ? 'Copy - ' .
                            $purchaseInvoice
                                ->remarks
                        : 'Copy Purchase Invoice',

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
                $purchaseInvoice->details
                as $detail
            ) {

                PurchaseInvoiceDetail::create([

                    'purchase_invoice_header_id' =>
                        $duplicate->id,

                    /*
                    |--------------------------------------------------------------------------
                    | 3-Way Matching
                    |--------------------------------------------------------------------------
                    */

                    'purchase_order_detail_id' =>
                        $detail
                            ->purchase_order_detail_id,

                    'goods_receipt_detail_id' =>
                        $detail
                            ->goods_receipt_detail_id,

                    /*
                    |--------------------------------------------------------------------------
                    | Product
                    |--------------------------------------------------------------------------
                    */

                    'product_variant_id' =>
                        $detail
                            ->product_variant_id,

                    'unit_id' =>
                        $detail
                            ->unit_id,

                    /*
                    |--------------------------------------------------------------------------
                    | Quantity
                    |--------------------------------------------------------------------------
                    */

                    'ordered_qty' =>
                        $detail
                            ->ordered_qty,

                    'received_qty' =>
                        $detail
                            ->received_qty,

                    'invoiced_qty' =>
                        $detail
                            ->invoiced_qty,

                    /*
                    |--------------------------------------------------------------------------
                    | Price
                    |--------------------------------------------------------------------------
                    */

                    'unit_price' =>
                        $detail
                            ->unit_price,

                    'discount_amount' =>
                        $detail
                            ->discount_amount,

                    'tax_amount' =>
                        $detail
                            ->tax_amount,

                    'subtotal' =>
                        $detail
                            ->subtotal,

                    'total_amount' =>
                        $detail
                            ->total_amount,

                    /*
                    |--------------------------------------------------------------------------
                    | Notes
                    |--------------------------------------------------------------------------
                    */

                    'remarks' =>
                        $detail
                            ->remarks,

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

                    'Purchase invoice duplicated.'

                );


            return $duplicate;

        }
    );
}
/*
|--------------------------------------------------------------------------
| Cancel
|--------------------------------------------------------------------------
*/

public function cancelPurchaseInvoice(
    PurchaseInvoiceHeader $purchaseInvoice,
    string $reason
): void {

    DB::transaction(
        function () use (
            $purchaseInvoice,
            $reason
        ) {

            /*
            |--------------------------------------------------------------------------
            | Lock Header
            |--------------------------------------------------------------------------
            */

            $purchaseInvoice =
                PurchaseInvoiceHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseInvoice->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                ! in_array(
                    $purchaseInvoice->status,
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
                    'Purchase invoice cannot be cancelled in its current status.'
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
                $purchaseInvoice->status;


            /*
            |--------------------------------------------------------------------------
            | Cancel
            |--------------------------------------------------------------------------
            */

            $purchaseInvoice->update([

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

                    $purchaseInvoice,

                    'CANCELLED',

                    $oldStatus,

                    'Cancelled',

                    'Purchase invoice cancelled.',

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

public function deletePurchaseInvoices(
    array $ids
): void {

    DB::transaction(
        function () use ($ids) {

            $purchaseInvoices =
                PurchaseInvoiceHeader::query()
                    ->whereIn(
                        'id',
                        $ids
                    )
                    ->lockForUpdate()
                    ->get();


            foreach (
                $purchaseInvoices
                as $purchaseInvoice
            ) {

                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $purchaseInvoice->status,
                        [
                            'Draft',
                            'Rejected',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Submitted or processed purchase invoice cannot be deleted.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Delete Details
                |--------------------------------------------------------------------------
                */

                $purchaseInvoice
                    ->details()
                    ->delete();


                /*
                |--------------------------------------------------------------------------
                | Soft Delete Header
                |--------------------------------------------------------------------------
                */

                $purchaseInvoice->delete();

            }

        }
    );
}
}