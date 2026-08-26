<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;

class GoodsReceiptDetail extends Model
{
    use HasFactory;

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        'goods_receipt_header_id',

        /*
        |--------------------------------------------------------------------------
        | Purchase Order
        |--------------------------------------------------------------------------
        */

        'purchase_order_detail_id',

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

        'rejected_qty',

           /*
        |--------------------------------------------------------------------------
        | Cost
        |--------------------------------------------------------------------------
        */
        'unit_cost',
        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        'remarks',

    ];

    protected $casts = [

        'ordered_qty' =>
            'decimal:6',

        'received_qty' =>
            'decimal:6',

        'rejected_qty' =>
            'decimal:6',
        
        'unit_cost' =>
            'decimal:2',

    ];

    /*
    |--------------------------------------------------------------------------
    | Goods Receipt
    |--------------------------------------------------------------------------
    */

    public function goodsReceipt()
    {
        return $this->belongsTo(
            GoodsReceiptHeader::class,
            'goods_receipt_header_id'
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
            Unit::class,
            'unit_id'
        );
    }
}