<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferDetail extends Model
{
    protected $fillable = [

        'stock_transfer_id',

        'product_id',

        'qty',

        'unit_cost',

        'total_cost',

        'remarks',

    ];

    public function transfer()
    {
        return $this->belongsTo(

            StockTransfer::class,

            'stock_transfer_id'

        );
    }

    public function product()
    {
        return $this->belongsTo(

            Product::class

        );
    }
}