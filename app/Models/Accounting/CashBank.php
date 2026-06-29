<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashBank extends Model
{
    use HasFactory;

    protected $fillable = [

        'code',

        'name',

        'type',

        'bank_name',

        'account_number',

        'account_holder',

        'opening_balance',

        'current_balance',

        'is_active',

        'remarks',

        'created_by',

        'updated_by',

    ];

    protected $casts = [

        'opening_balance' => 'decimal:2',

        'current_balance' => 'decimal:2',

        'is_active' => 'boolean',

    ];

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

    public function supplierPayments()
    {
        return $this->hasMany(

            SupplierPayment::class,

            'cash_bank_id'

        );
    }
}