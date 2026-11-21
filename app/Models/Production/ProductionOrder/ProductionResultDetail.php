<?php

namespace App\Models\Production\ProductionOrder;

use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionResultDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_result_id',
        'product_variant_id',
        'unit_id',
        'produced_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'qc_inspected_by',
        'qc_inspected_at',
        'qc_notes',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'produced_quantity' => 'decimal:6',
            'accepted_quantity' => 'decimal:6',
            'rejected_quantity' => 'decimal:6',
            'qc_inspected_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function productionResult(): BelongsTo
    {
        return $this->belongsTo(
            ProductionResult::class,
            'production_result_id'
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

    public function qcInspector(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'qc_inspected_by'
        );
    }
}