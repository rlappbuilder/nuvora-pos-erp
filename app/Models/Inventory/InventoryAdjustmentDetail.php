<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;
use App\Models\User;

class InventoryAdjustmentDetail extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        'inventory_adjustment_header_id',


        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        'product_variant_id',

        'unit_id',


        /*
        |--------------------------------------------------------------------------
        | Quantity
        |--------------------------------------------------------------------------
        */

        'system_qty',

        'actual_qty',

        'difference_qty',


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

        'description',


        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        'created_by',

        'updated_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'system_qty' => 'decimal:2',

        'actual_qty' => 'decimal:2',

        'difference_qty' => 'decimal:2',

        'unit_cost' => 'decimal:2',

        'total_cost' => 'decimal:2',

    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function header(): BelongsTo
    {
        return $this->belongsTo(
            InventoryAdjustmentHeader::class,
            'inventory_adjustment_header_id'
        );
    }


    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }


    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            Unit::class
        );
    }


    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }
}