<?php

namespace App\Models\Product;

use App\Models\User;
use App\Models\MasterData\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttribute extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'display_name',
        'input_type',
        'is_required',
        'is_variant',
        'sort_order',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_variant'  => 'boolean',
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company():BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTO
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater():BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter():BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function values():HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
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

    public function scopeVariant(Builder $query): Builder
    {
        return $query->where('is_variant', true);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('display_name', 'like', "%{$search}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    public function isUsed(): bool
    {
        return $this->values()->exists();
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
        $this->update([
            'is_active' => true,
        ]);
    }

    public function deactivate(): bool
    {
        $this->update([
            'is_active' => false,
        ]);
    }
    public function duplicateData(): array
    {
        return [
            'company_id' => $this->company_id,
            'code' => null,
            'name' => $this->name . ' (Copy)',
            'display_name' => $this->display_name,
            'input_type' => $this->input_type,
            'is_required' => $this->is_required,
            'is_variant' => $this->is_variant,
            'sort_order' => $this->sort_order,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];
    }
}