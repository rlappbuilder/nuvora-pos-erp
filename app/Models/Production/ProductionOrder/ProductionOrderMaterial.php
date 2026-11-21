<?php

namespace App\Models\Production\ProductionOrder;

use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;
use App\Models\Production\ProductionBomMaterial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_header_id',
        'production_bom_material_id',
        'production_order_operation_id',
        'component_variant_id',
        'unit_id',
        'bom_qty',
        'scrap_percent',
        'required_quantity',
        'issued_quantity',
        'returned_quantity',
        'sequence',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'bom_qty'           => 'decimal:4',
            'scrap_percent'     => 'decimal:4',
            'required_quantity' => 'decimal:6',
            'issued_quantity'   => 'decimal:6',
            'returned_quantity' => 'decimal:6',
            'sequence'          => 'integer',
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

    public function productionBomMaterial(): BelongsTo
    {
        return $this->belongsTo(
            ProductionBomMaterial::class,
            'production_bom_material_id'
        );
    }

    public function productionOrderOperation(): BelongsTo
    {
        return $this->belongsTo(
            ProductionOrderOperation::class,
            'production_order_operation_id'
        );
    }

    public function componentVariant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'component_variant_id'
        );
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}