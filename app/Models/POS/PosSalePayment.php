<?php

namespace App\Models\POS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSalePayment extends Model
{
    protected $table = 'pos_sale_payments';

    protected $fillable = [
        'pos_sale_id',
        'payment_method',
        'amount',
        'reference_no',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }
}