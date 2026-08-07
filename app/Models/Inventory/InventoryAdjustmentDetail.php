<?php

namespace App\Models\Inventory;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryAdjustmentDetail extends Model
{
    use HasFactory;

    protected $fillable = [

        'inventory_adjustment_id',

        'product_id',

        'system_qty',

        'physical_qty',

        'difference_qty',

        'unit_cost',

        'remarks',

        

    ];

    public function adjustment()
    {
        return $this->belongsTo(

            InventoryAdjustment::class,

            'inventory_adjustment_id'

        );
    }

    public function product()
    {
        return $this->belongsTo(

            Product::class

        );
    }
}