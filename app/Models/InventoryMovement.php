<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [

        'product_id',

        'warehouse_id',

        'reference_type',

        'reference_id',

        'reference_number',

        'qty_in',

        'qty_out',

        'balance_qty',

        'transaction_date',

        'created_by',

    ];

    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }

    public function warehouse()
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }
}