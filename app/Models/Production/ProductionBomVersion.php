<?php

namespace App\Models\Production;

use App\Models\MasterData\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionBomVersion extends Model
{
    protected $table = 'production_bom_versions';

    protected $fillable = [
        'production_bom_id',
        'version',
        'output_qty',
        'output_unit_id',
        'effective_from',
        'effective_until',
        'status',
        'revision_notes',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'output_qty' => 'decimal:4',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'approved_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function productionBom(): BelongsTo
    {
        return $this->belongsTo(
            ProductionBom::class,
            'production_bom_id'
        );
    }

    public function outputUnit(): BelongsTo
    {
        return $this->belongsTo(
            Unit::class,
            'output_unit_id'
        );
    }

    public function materials(): HasMany
    {
        return $this->hasMany(
            ProductionBomMaterial::class,
            'production_bom_version_id'
        )->orderBy('sequence');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(
            ProductionBomOperation::class,
            'production_bom_version_id'
        )->orderBy('sequence');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByBom(
        Builder $query,
        int $bomId
    ): Builder {
        return $query->where('production_bom_id', $bomId);
    }
}