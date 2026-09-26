<?php

namespace App\Models\POS;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\POS\CashierDeposit;
use App\Models\MasterData\Warehouse;
class CashierSession extends Model
{
    protected $table = 'cashier_sessions';

    protected $fillable = [
        'company_id',
        'branch_id',
        'user_id',
        'cash_account_id',
        'previous_session_id',
        'session_number',
        'opened_at',
        'opening_balance',
        'closed_at',
        'closing_balance',
        'status',
         'warehouse_id',
        'closing_note',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Branch
    |--------------------------------------------------------------------------
    */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(
            Branch::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cashier User
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Cash Account
    |--------------------------------------------------------------------------
    */

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\Accounting\ChartOfAccount::class,
            'cash_account_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Previous Session
    |--------------------------------------------------------------------------
    */

    public function previousSession(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'previous_session_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Status Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOpen($query)
    {
        return $query->where(
            'status',
            'open'
        );
    }

    public function scopeClosed($query)
    {
        return $query->where(
            'status',
            'closed'
        );
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(
            CashierDeposit::class
        );
    }
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}