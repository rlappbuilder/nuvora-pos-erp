<?php

namespace App\Models\Production;

use App\Models\Product\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeProductionOperationRate extends Model
{
    use SoftDeletes;

    protected $table = 'employee_production_operation_rates';

    protected $fillable = [
        'employee_production_operation_id',
        'product_variant_id',
        'wage_rate',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'wage_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function employeeProductionOperation(): BelongsTo
    {
        return $this->belongsTo(
            EmployeeProductionOperation::class,
            'employee_production_operation_id'
        );
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByProductVariant(
        Builder $query,
        int $productVariantId
    ): Builder {
        return $query->where(
            'product_variant_id',
            $productVariantId
        );
    }
}