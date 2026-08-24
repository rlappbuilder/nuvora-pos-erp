<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        'purchase_order_id',

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

        'qty',

        'received_qty',

        'remaining_qty',

        /*
        |--------------------------------------------------------------------------
        | Pricing
        |--------------------------------------------------------------------------
        */

        'unit_price',

        'discount_rate',

        'discount_amount',

        'tax_rate',

        'tax_amount',

        'subtotal',

        'total',

        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        'description',

    ];

    protected $casts = [

        'qty' =>
            'decimal:6',

        'received_qty' =>
            'decimal:6',

        'remaining_qty' =>
            'decimal:6',

        'unit_price' =>
            'decimal:2',

        'discount_rate' =>
            'decimal:4',

        'discount_amount' =>
            'decimal:2',

        'tax_rate' =>
            'decimal:4',

        'tax_amount' =>
            'decimal:2',

        'subtotal' =>
            'decimal:2',

        'total' =>
            'decimal:2',

    ];

    /*
    |--------------------------------------------------------------------------
    | Purchase Order
    |--------------------------------------------------------------------------
    */

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrderHeader::class,
            'purchase_order_id'
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