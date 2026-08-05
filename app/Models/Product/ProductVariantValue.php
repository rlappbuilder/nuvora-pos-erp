<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantValue extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'product_variant_id',
        'product_attribute_id',
        'product_attribute_value_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeValue::class);
    }
}