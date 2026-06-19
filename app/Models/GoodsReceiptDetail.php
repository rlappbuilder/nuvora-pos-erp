<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsReceiptDetail extends Model
{
    use HasFactory;

    protected $fillable = [

        'goods_receipt_id',

        'product_id',

        'qty_received',

        'unit_cost',

        'line_total',

    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(
            GoodsReceipt::class
        );
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }
}