<?php

namespace App\Models\Production;

use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionBomMaterial extends Model
{
    protected $table = 'production_bom_materials';

    protected $fillable = [
        'production_bom_version_id',
        'component_variant_id',
        'unit_id',
        'qty',
        'scrap_percent',
        'production_bom_operation_id',
        'sequence',
        'notes',
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'scrap_percent' => 'decimal:4',
        'sequence' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function productionBomVersion(): BelongsTo
    {
        return $this->belongsTo(
            ProductionBomVersion::class,
            'production_bom_version_id'
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
        return $this->belongsTo(Unit::class);
    }

    public function productionBomOperation(): BelongsTo
    {
        return $this->belongsTo(
            ProductionBomOperation::class,
            'production_bom_operation_id'
        );
    }
}