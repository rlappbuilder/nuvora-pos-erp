<?php

namespace App\Models\Reseller;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResellerPriceHistory extends Model
{
    use HasFactory;

    protected $table = 'reseller_price_histories';

    protected $fillable = [
        'reseller_id',
        'product_id',
        'price',
        'effective_from',
        'effective_to',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'effective_from' => 'datetime',
        'effective_to' => 'datetime',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}