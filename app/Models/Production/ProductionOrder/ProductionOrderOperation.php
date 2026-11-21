<?php

namespace App\Models\Production\ProductionOrder;

use App\Models\Production\ProductionBomOperation;
use App\Models\Production\ProductionOperation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ProductionOrderOperation extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_header_id',
        'production_bom_operation_id',
        'production_operation_id',
        'sequence',
        'planned_duration_minutes',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'planned_duration_minutes' => 'decimal:2',
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

    public function productionBomOperation(): BelongsTo
    {
        return $this->belongsTo(
            ProductionBomOperation::class,
            'production_bom_operation_id'
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
        ProductionOrderMaterial::class,
        'production_order_operation_id'
    )->orderBy('sequence');
}
}