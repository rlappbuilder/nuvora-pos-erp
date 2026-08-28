<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;

class PurchaseReturnDetail extends Model
{
    use HasFactory;


    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        'purchase_return_header_id',


        /*
        |--------------------------------------------------------------------------
        | Goods Receipt Detail
        |--------------------------------------------------------------------------
        */

        'goods_receipt_detail_id',


        /*
        |--------------------------------------------------------------------------
        | Purchase Order Detail
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

        'received_qty',

        'returned_qty',


        /*
        |--------------------------------------------------------------------------
        | Cost
        |--------------------------------------------------------------------------
        */

        'unit_cost',

        'total_cost',


        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        'remarks',

    ];


    protected $casts = [

        'received_qty' =>
            'decimal:6',

        'returned_qty' =>
            'decimal:6',

        'unit_cost' =>
            'decimal:2',

        'total_cost' =>
            'decimal:2',

    ];


    /*
    |--------------------------------------------------------------------------
    | Purchase Return
    |--------------------------------------------------------------------------
    */

    public function purchaseReturn()
    {
        return $this->belongsTo(
            PurchaseReturnHeader::class,
            'purchase_return_header_id'
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