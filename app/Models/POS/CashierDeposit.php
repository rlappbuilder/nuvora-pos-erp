<?php

namespace App\Models\POS;

use App\Models\Accounting\ChartOfAccount;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Core\DocumentActivity;
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

    'submitted_at',
    'submitted_by',

    'approved_at',
    'approved_by',

    'rejected_at',
    'rejected_by',
    'rejected_reason',

    'received_at',
    'received_by',

    'posted_at',
    'posted_by',

    'cancelled_at',
    'cancelled_by',
    'cancelled_reason',

    'created_by',
    'updated_by',
];

    protected $casts = [
    'amount' => 'decimal:2',
    'deposited_at' => 'datetime',

    'submitted_at' => 'datetime',
    'approved_at' => 'datetime',
    'rejected_at' => 'datetime',
    'received_at' => 'datetime',
    'posted_at' => 'datetime',
    'cancelled_at' => 'datetime',
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
    public function submittedBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'submitted_by'
    );
}

public function approvedBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'approved_by'
    );
}

public function rejectedBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'rejected_by'
    );
}

public function receivedBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'received_by'
    );
}

public function postedBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'posted_by'
    );
}

public function cancelledBy(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'cancelled_by'
    );
}
public function activities()
{
    return $this->hasMany(
        DocumentActivity::class,
        'document_id'
    )
    ->where(
        'document_type',
        class_basename($this)
    )
    ->orderBy(
        'performed_at',
        'asc'
    );
}
}