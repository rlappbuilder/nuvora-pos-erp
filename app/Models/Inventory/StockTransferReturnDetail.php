<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;
use App\Models\User;

class StockTransferReturnDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_transfer_return_details';

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'stock_transfer_return_header_id',
        'stock_transfer_detail_id',

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
        'qty' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Return Header
    |--------------------------------------------------------------------------
    */

    public function stockTransferReturnHeader(): BelongsTo
    {
        return $this->belongsTo(
            StockTransferReturnHeader::class,
            'stock_transfer_return_header_id',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Original Stock Transfer Detail
    |--------------------------------------------------------------------------
    */

    public function stockTransferDetail(): BelongsTo
    {
        return $this->belongsTo(
            StockTransferDetail::class,
            'stock_transfer_detail_id',
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
        return $this->belongsTo(Unit::class);
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