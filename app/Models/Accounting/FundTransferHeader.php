<?php

namespace App\Models\Accounting;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Core\DocumentActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
class FundTransferHeader extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fund_transfer_headers';

    protected $fillable = [
        'company_id',

        'transfer_number',
        'transfer_date',

        'from_branch_id',
        'from_account_id',

        'to_branch_id',
        'to_account_id',

        'amount',
        'description',

        'status',

        'journal_entry_id',

        'submitted_at',
        'submitted_by',

        'approved_at',
        'approved_by',

        'rejected_at',
        'rejected_by',
        'rejected_reason',

        'posted_at',
        'posted_by',

        'cancelled_at',
        'cancelled_by',
        'cancelled_reason',

        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',

        'amount' => 'decimal:2',

        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
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
    | Source Branch
    |--------------------------------------------------------------------------
    */

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(
            Branch::class,
            'from_branch_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Source Account
    |--------------------------------------------------------------------------
    */

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(
            ChartOfAccount::class,
            'from_account_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Destination Branch
    |--------------------------------------------------------------------------
    */

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(
            Branch::class,
            'to_branch_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Destination Account
    |--------------------------------------------------------------------------
    */

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(
            ChartOfAccount::class,
            'to_account_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow Users
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Audit Users
    |--------------------------------------------------------------------------
    */

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

     /*
    |--------------------------------------------------------------------------
    | Document Activities
    |--------------------------------------------------------------------------
    */

    public function activities(): HasMany
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->whereNotIn(
            'status',
            [
                'Cancelled',
            ]
        );
    }

    public function scopeByCompany(
        Builder $query,
        int $companyId
    ): Builder {
        return $query->where(
            'company_id',
            $companyId
        );
    }

    public function scopeByStatus(
        Builder $query,
        string $status
    ): Builder {
        return $query->where(
            'status',
            $status
        );
    }

    /*
|--------------------------------------------------------------------------
| Journal Entry
|--------------------------------------------------------------------------
*/

public function journalEntry(): BelongsTo
{
    return $this->belongsTo(
        JournalEntry::class,
        'journal_entry_id'
    );
}
}