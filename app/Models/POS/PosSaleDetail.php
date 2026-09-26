<?php

namespace App\Models\POS;

use App\Models\MasterData\Unit;
use App\Models\Product\ProductVariant;
use App\Models\MasterData\PriceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSaleDetail extends Model
{
    protected $table = 'pos_sale_details';

    protected $fillable = [
        'pos_sale_id',
        'product_variant_id',
        'unit_id',
        'price_type_id',
        'qty',
        'unit_price',
        'discount_amount',
        'subtotal',
        'unit_cost',
        'total_cost',
    ];

    protected $casts = [
        'qty' => 'decimal:6',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function priceType(): BelongsTo
    {
        return $this->belongsTo(PriceType::class);
    }
}