<?php

namespace App\Models\Reseller\ConsignmentReturn;

use App\Models\Reseller\ResellerStockPriceLayer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentReturnPriceLayer extends Model
{
    use HasFactory;

    protected $table = 'consignment_return_price_layers';

    protected $fillable = [
        'return_detail_id',
        'price_layer_id',
        'qty',
        'unit_price',
        'total_value',
    ];

    protected $casts = [
        'qty' => 'decimal:6',
        'unit_price' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function returnDetail(): BelongsTo
    {
        return $this->belongsTo(
            ConsignmentReturnDetail::class,
            'return_detail_id'
        );
    }

    public function priceLayer(): BelongsTo
    {
        return $this->belongsTo(
            ResellerStockPriceLayer::class,
            'price_layer_id'
        );
    }
}