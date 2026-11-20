<?php

namespace App\Models\Production;

use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\Product\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Core\DocumentActivity;
class ProductionBom extends Model
{
    use SoftDeletes;

    protected $table = 'production_boms';

    protected $fillable = [
        'company_id',
        'branch_id',
        'product_variant_id',
        'code',
        'name',
        'bom_type',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }

    public function versions(): HasMany
    {
        return $this->hasMany(
            ProductionBomVersion::class,
            'production_bom_id'
        )->orderByDesc('version');
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

    public function scopeByCompany(
        Builder $query,
        int $companyId
    ): Builder {
        return $query->where('company_id', $companyId);
    }

    public function scopeByBranch(
        Builder $query,
        int $branchId
    ): Builder {
        return $query->where('branch_id', $branchId);
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

    public function scopeStandard(Builder $query): Builder
    {
        return $query->where('bom_type', 'standard');
    }

    public function scopeAlternative(Builder $query): Builder
    {
        return $query->where('bom_type', 'alternative');
    }

    public function activities(): HasMany
{
    return $this->hasMany(
        DocumentActivity::class,
        'document_id'
    )
        ->where('document_type', class_basename($this))
        ->orderBy('performed_at', 'asc');
}
}