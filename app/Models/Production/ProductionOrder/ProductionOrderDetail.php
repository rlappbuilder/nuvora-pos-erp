<?php

namespace App\Models\Production\ProductionOrder;

use App\Models\MasterData\Unit;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_header_id',
        'product_variant_id',
        'unit_id',
        'planned_quantity',
        'produced_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'planned_quantity'  => 'decimal:6',
            'produced_quantity' => 'decimal:6',
            'accepted_quantity' => 'decimal:6',
            'rejected_quantity' => 'decimal:6',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(
            ProductionOrderHeader::class,
            'production_order_header_id'
        );
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}