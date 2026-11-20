<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionBomOperation extends Model
{
    protected $table = 'production_bom_operations';

    protected $fillable = [
        'production_bom_version_id',
        'production_operation_id',
        'sequence',
        'planned_duration_minutes',
        'instructions',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'planned_duration_minutes' => 'decimal:2',
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

    public function productionOperation(): BelongsTo
    {
        return $this->belongsTo(
            ProductionOperation::class,
            'production_operation_id'
        );
    }

    public function materials(): HasMany
    {
        return $this->hasMany(
            ProductionBomMaterial::class,
            'production_bom_operation_id'
        )->orderBy('sequence');
    }
}