<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [

        'product_id',
        'warehouse_id',

        'transaction_type',

        'reference_no',

        'qty',

        'unit_cost',

        'remarks',

        'created_by',

    ];
}