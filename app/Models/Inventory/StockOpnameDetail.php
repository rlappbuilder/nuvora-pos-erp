<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;
use App\Models\User;

class StockOpnameDetail extends Model
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

        'stock_opname_header_id',


        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        'product_variant_id',

        'unit_id',


        /*
        |--------------------------------------------------------------------------
        | Stock
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

        'difference_cost',


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

        'system_qty' =>
            'decimal:2',

        'actual_qty' =>
            'decimal:2',

        'difference_qty' =>
            'decimal:2',

        'unit_cost' =>
            'decimal:2',

        'difference_cost' =>
            'decimal:2',

    ];


    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    public function stockOpnameHeader(): BelongsTo
    {
        return $this->belongsTo(
            StockOpnameHeader::class,
            'stock_opname_header_id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Product Variant
    |--------------------------------------------------------------------------
    */

    public function variant(): BelongsTo
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

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            Unit::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Created / Updated By
    |--------------------------------------------------------------------------
    */

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