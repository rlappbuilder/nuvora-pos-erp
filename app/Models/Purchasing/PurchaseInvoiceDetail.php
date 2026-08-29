<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;

class PurchaseInvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        'purchase_invoice_header_id',

        /*
        |--------------------------------------------------------------------------
        | 3-Way Matching
        |--------------------------------------------------------------------------
        */

        'purchase_order_detail_id',

        'goods_receipt_detail_id',

        /*
        |--------------------------------------------------------------------------
        | Product
        |--------------------------------------------------------------------------
        */

        'product_variant_id',

        'unit_id',

        /*
        |--------------------------------------------------------------------------
        | Quantity
        |--------------------------------------------------------------------------
        */

        'ordered_qty',

        'received_qty',

        'invoiced_qty',

        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        */

        'unit_price',

        'discount_amount',

        'tax_amount',

        'subtotal',

        'total_amount',

        /*
        |--------------------------------------------------------------------------
        | Notes
        |--------------------------------------------------------------------------
        */

        'remarks',

    ];

    protected $casts = [

        /*
        |--------------------------------------------------------------------------
        | Quantity
        |--------------------------------------------------------------------------
        */

        'ordered_qty' =>
            'decimal:6',

        'received_qty' =>
            'decimal:6',

        'invoiced_qty' =>
            'decimal:6',

        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        */

        'unit_price' =>
            'decimal:2',

        'discount_amount' =>
            'decimal:2',

        'tax_amount' =>
            'decimal:2',

        'subtotal' =>
            'decimal:2',

        'total_amount' =>
            'decimal:2',

    ];

    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    public function purchaseInvoice()
    {
        return $this->belongsTo(
            PurchaseInvoiceHeader::class,
            'purchase_invoice_header_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Purchase Order Detail
    |--------------------------------------------------------------------------
    */

    public function purchaseOrderDetail()
    {
        return $this->belongsTo(
            PurchaseOrderDetail::class,
            'purchase_order_detail_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Goods Receipt Detail
    |--------------------------------------------------------------------------
    */

    public function goodsReceiptDetail()
    {
        return $this->belongsTo(
            GoodsReceiptDetail::class,
            'goods_receipt_detail_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Product Variant
    |--------------------------------------------------------------------------
    */

    public function productVariant()
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Unit
    |--------------------------------------------------------------------------
    */

    public function unit()
    {
        return $this->belongsTo(
            Unit::class
        );
    }
}