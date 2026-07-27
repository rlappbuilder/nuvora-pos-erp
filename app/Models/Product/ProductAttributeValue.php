<?php

namespace App\Models\Product;

use App\Models\User;
use App\Models\MasterData\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product\ProductAttribute;

class ProductAttributeValue extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'product_attribute_id',

        'code',
        'value',
        'display_value',

        'color_code',

        'sort_order',

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

    public function productAttribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class);
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

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $query) use ($search) {

            $query->where(function (Builder $query) use ($search) {

                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%")
                    ->orWhere('display_value', 'like', "%{$search}%");

            });

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
        return !$this->isUsed();
    }

    public function canActivate(): bool
    {
        return !$this->is_active;
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

    public function duplicateData(): self
    {
        $duplicate = $this->replicate();

        $duplicate->code = null;

        $duplicate->value = $this->value . ' Copy';

        $duplicate->display_value = $this->display_value . ' Copy';

        $duplicate->created_by = auth()->id();

        $duplicate->updated_by = null;

        $duplicate->save();

        return $duplicate;
    }
    public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}
}