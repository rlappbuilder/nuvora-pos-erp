<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\MasterData\Category;
use App\Models\MasterData\Brand;
use App\Models\MasterData\Unit;
use App\Models\User;
use App\Models\Inventory\ProductStock;
use App\Models\Inventory\StockMovement;
use App\Models\Inventory\InventoryMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasSlug;
use App\Models\Product\ProductAttribute;
use App\Models\Product\ProductAttributeAssignment;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity,HasSlug;
    protected $table = 'products';
    protected $fillable = [

    'category_id',
    'brand_id',
    'unit_id',

    'code',
    'sku',
    'slug',

    'name',

    'description',

    'image',

    'product_type',

    'track_stock',

    'is_sellable',

    'is_purchasable',

    'minimum_stock',

    'is_active',

    'created_by',
    'updated_by',
    'deleted_by',

];
   protected $casts = [

    'track_stock' => 'boolean',

    'is_sellable' => 'boolean',

    'is_purchasable' => 'boolean',

    'is_active' => 'boolean',

];

public function unit()
{
    return $this->belongsTo(
        Unit::class
    );
}

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            Category::class
        );
    }

    public function brand()
    {
        return $this->belongsTo(
            Brand::class
        );
    }
    public function stocks()
{
    return $this->hasMany(
        ProductStock::class
    );
}
public function stockMovements()
{
    return $this->hasMany(
        StockMovement::class
    );
}

public function inventoryMovements()
{
    return $this->hasMany(
        InventoryMovement::class
    );
}
public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logFillable()
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}
public function scopeSearch(Builder $query, ?string $search): Builder
{
    return $query->when($search, function (Builder $query) use ($search) {
        $query->where(function (Builder $query) use ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%");
        });
    });
}

public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}

public function scopeInactive(Builder $query): Builder
{
    return $query->where('is_active', false);
}
public function canDelete(): bool
{
 //   if ($this->variants()->exists()) {
  //      return false;
    //}

    //if ($this->warehouseStocks()->exists()) {
     //   return false;
    //}

    //if ($this->purchaseDetails()->exists()) {
      //  return false;
    //}

    //if ($this->salesDetails()->exists()) {
      //  return false;
  //  }

    return true;
}

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function updater()
{
    return $this->belongsTo(User::class, 'updated_by');
}

public function deleter()
{
    return $this->belongsTo(User::class, 'deleted_by');
}
public static function generateSku(): string
{
    do {
        $sku = strtoupper(fake()->bothify('SKU-########'));
    } while (self::where('sku', $sku)->exists());

    return $sku;
}
public static function generateSlug(
    string $name,
    ?int $ignoreId = null
): string {
    $slug = Str::slug($name);

    $originalSlug = $slug;
    $counter = 1;

    while (
        self::where('slug', $slug)
            ->when(
                $ignoreId,
                fn ($q) => $q->where('id', '!=', $ignoreId)
            )
            ->exists()
    ) {
        $slug = "{$originalSlug}-{$counter}";
        $counter++;
    }

    return $slug;
}
public function variants(): HasMany
{
    return $this->hasMany(ProductVariant::class);
}
public function attributeAssignments(): HasMany
{
    return $this->hasMany(
        ProductAttributeAssignment::class
    );
}
public function attributes(): BelongsToMany
{
    return $this->belongsToMany(
        ProductAttribute::class,
        'product_attribute_assignments'
    );
}
}