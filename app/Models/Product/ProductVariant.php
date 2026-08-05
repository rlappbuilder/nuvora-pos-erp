<?php

namespace App\Models\Product;

use App\Models\User;
use App\Models\MasterData\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Product\ProductVariantUnit;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        
        'product_id',

        'sku',
        'barcode',

        'name',
        'slug',

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
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProductVariantValue::class);
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

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isUsed(): bool
    {
        return false;
    }

    public function canDelete(): bool
    {
        return ! $this->isUsed();
    }

    public function canActivate(): bool
    {
        return ! $this->is_active;
    }

    public function canDeactivate(): bool
    {
        return $this->is_active;
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
    public function units(): HasMany
{
    return $this->hasMany(
        ProductVariantUnit::class
    );
}
}