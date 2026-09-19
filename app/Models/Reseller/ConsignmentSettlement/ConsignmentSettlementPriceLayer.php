<?php

namespace App\Models\Reseller\ConsignmentSettlement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Reseller\ResellerStockPriceLayer;

class ConsignmentSettlementPriceLayer extends Model
{
    use HasFactory;

    protected $table =
        'consignment_settlement_price_layers';

    protected $fillable = [
        'settlement_detail_id',
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

    public function settlementDetail(): BelongsTo
    {
        return $this->belongsTo(
            ConsignmentSettlementDetail::class,
            'settlement_detail_id'
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