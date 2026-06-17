<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}