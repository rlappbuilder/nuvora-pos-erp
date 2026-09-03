<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Core\DocumentActivity;
use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\User;
use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\AccountingPeriod;

class JournalEntry extends Model
{
    use SoftDeletes;

    protected $table = 'journal_entries';

    protected $fillable = [
        'company_id',
        'branch_id',
        'accounting_journal_id',
        'fiscal_year_id',
        'accounting_period_id',
        'code',
        'entry_date',
        'reference',
        'description',
        'status',
        'posted_by',
        'posted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Organization
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accounting Structure
    |--------------------------------------------------------------------------
    */

    public function accountingJournal(): BelongsTo
    {
        return $this->belongsTo(AccountingJournal::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Lines
    |--------------------------------------------------------------------------
    */

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class)
            ->orderBy('line_number');
    }

    /*
    |--------------------------------------------------------------------------
    | Posting
    |--------------------------------------------------------------------------
    */

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    */

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /*
|--------------------------------------------------------------------------
| General Ledger
|--------------------------------------------------------------------------
*/

public function ledgerEntries(): HasMany
{
    return $this->hasMany(
        GeneralLedger::class,
        'journal_entry_id'
    );
}
/*
|--------------------------------------------------------------------------
| Workflow Activities
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
}