<?php

namespace App\Models\Reseller\ConsignmentSettlement;

use App\Models\MasterData\Unit;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentSettlementDetail extends Model
{
    use HasFactory;

    protected $table = 'settlement_details';

    protected $fillable = [
        'settlement_header_id',
        'product_variant_id',
        'unit_id',
        'qty_sold',
        'unit_price',
        'total_amount',
    ];

    protected $casts = [
        'qty_sold' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(
            ConsignmentSettlementHeader::class,
            'settlement_header_id'
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
        return $this->belongsTo(Unit::class);
    }
}