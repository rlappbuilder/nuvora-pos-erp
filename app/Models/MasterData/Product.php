<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'category_id',
        'brand_id',

        'sku',
        'barcode',

        'name',
        'description',

        'image',
        'product_type',
        
        'unit',

        'cost_price',
        'selling_price',

        'minimum_stock',

        'status',

        'created_by',
        'updated_by',


    ];

    protected $casts = [

        'cost_price' => 'decimal:2',

        'selling_price' => 'decimal:2',

        'status' => 'boolean',

    ];

    public function category()
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
public function movements()
{
    return $this->hasMany(
        InventoryMovement::class
    );
}
public function inventoryMovements()
{
    return $this->hasMany(
        InventoryMovement::class
    );
}
}