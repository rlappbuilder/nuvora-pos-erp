<?php

namespace App\Models\Product;

use App\Models\User;
use App\Models\MasterData\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantUnit extends Model
{
    use HasFactory;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'product_variant_id',
        'unit_id',

        'conversion_factor',

        'is_base',
        'is_default',
        'is_active',

        'sort_order',

        'created_by',
        'updated_by',
        'deleted_by',

    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'conversion_factor' => 'decimal:6',

        'is_base' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',

        'sort_order' => 'integer',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(
            Unit::class
        );
    }

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

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'deleted_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder
    {
        return $query->where(
            'is_active',
            true
        );
    }

    public function scopeBase(
        Builder $query
    ): Builder
    {
        return $query->where(
            'is_base',
            true
        );
    }

    public function scopeDefault(
        Builder $query
    ): Builder
    {
        return $query->where(
            'is_default',
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function canDelete(): bool
    {
        return true;
    }

    public function activate(): bool
    {
        return $this->update([
            'is_active' => true,
        ]);
    }

    public function deactivate(): bool
    {
        return $this->update([
            'is_active' => false,
        ]);
    }
}