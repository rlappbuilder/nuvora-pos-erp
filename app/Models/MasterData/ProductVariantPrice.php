<?php

namespace App\Models\MasterData;

use App\Models\User;
use App\Models\Product\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariantPrice extends Model
{
    use SoftDeletes;

    protected $fillable = [

        'branch_id',

        'product_variant_id',

        'unit_id',

        'price_type_id',

        'last_purchase_price',

        'selling_price',

        'effective_from',

        'effective_until',

        'is_active',

        'description',

        'created_by',

        'updated_by',

        'deleted_by',

    ];

    protected $casts = [

        'last_purchase_price' => 'decimal:2',

        'selling_price' => 'decimal:2',

        'effective_from' => 'date',

        'effective_until' => 'date',

        'is_active' => 'boolean',

    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function variant()
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function priceType()
    {
        return $this->belongsTo(
            PriceType::class
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updater()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function deleter()
    {
        return $this->belongsTo(
            User::class,
            'deleted_by'
        );
    }
}