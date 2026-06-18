<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [

        'purchase_order_id',

        'product_id',

        'qty',

        'unit_cost',

        'discount',

        'tax',

        'line_total',

    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class
        );
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }
}