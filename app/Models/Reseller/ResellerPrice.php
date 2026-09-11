<?php

namespace App\Models\Reseller;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
class ResellerPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reseller_prices';

    protected $fillable = [
        'reseller_id',
        'product_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(
            Reseller::class
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class
        );
    }
}