<?php

namespace App\Models\Inventory;

use App\Models\MasterData\Unit;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIssueDetail extends Model
{
    use HasFactory;


    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'stock_issue_details';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'stock_issue_header_id',

        'product_variant_id',

        'unit_id',

        'qty',

        'unit_cost',

        'total_cost',

        'description',

        'created_by',

        'updated_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'qty' =>
            'decimal:4',

        'unit_cost' =>
            'decimal:4',

        'total_cost' =>
            'decimal:4',

    ];


    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    public function header(): BelongsTo
    {
        return $this->belongsTo(
            StockIssueHeader::class,
            'stock_issue_header_id'
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
            Unit::class,
            'unit_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Creator
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\User::class,
            'created_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Updater
    |--------------------------------------------------------------------------
    */

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\User::class,
            'updated_by'
        );
    }
}