<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    protected $table = 'payment_terms';

    protected $fillable = [
        'code',
        'name',
        'days',
        'description',
        'status',
    ];

    protected $casts = [
        'days' => 'integer',
        'status' => 'boolean',
    ];
}