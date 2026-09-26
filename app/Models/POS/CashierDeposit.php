<?php

namespace App\Models\POS;

use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierDeposit extends Model
{
    protected $table = 'cashier_deposits';

    protected $fillable = [
        'company_id',
        'branch_id',
        'cashier_session_id',
        'deposit_number',
        'destination_account_id',
        'amount',
        'deposited_at',
        'status',
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deposited_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(
            Branch::class
        );
    }

    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(
            CashierSession::class
        );
    }

    public function destinationAccount(): BelongsTo
    {
        return $this->belongsTo(
            ChartOfAccount::class,
            'destination_account_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function scopeDraft($query)
    {
        return $query->where(
            'status',
            'draft'
        );
    }

    public function scopePosted($query)
    {
        return $query->where(
            'status',
            'posted'
        );
    }

    public function scopeCancelled($query)
    {
        return $query->where(
            'status',
            'cancelled'
        );
    }
}