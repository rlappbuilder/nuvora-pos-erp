<?php

namespace App\Models\Accounting;

use App\Models\Accounting\AccountingJournal;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneralLedger extends Model
{
    protected $table = 'general_ledgers';

    protected $fillable = [
        'company_id',
        'branch_id',
        'account_id',
        'journal_entry_id',
        'journal_entry_line_id',
        'accounting_journal_id',
        'fiscal_year_id',
        'accounting_period_id',
        'entry_date',
        'reference',
        'description',
        'debit',
        'credit',
        'running_balance',
        'created_by',
    ];

    protected $casts = [
        'entry_date'      => 'date',
        'debit'           => 'decimal:2',
        'credit'          => 'decimal:2',
        'running_balance' => 'decimal:2',
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
    | Account
    |--------------------------------------------------------------------------
    */

    public function account(): BelongsTo
    {
        return $this->belongsTo(
            ChartOfAccount::class,
            'account_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Source Journal Entry
    |--------------------------------------------------------------------------
    */

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(
            JournalEntry::class,
            'journal_entry_id'
        );
    }

    public function journalEntryLine(): BelongsTo
    {
        return $this->belongsTo(
            JournalEntryLine::class,
            'journal_entry_line_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accounting Structure
    |--------------------------------------------------------------------------
    */

    public function accountingJournal(): BelongsTo
    {
        return $this->belongsTo(
            AccountingJournal::class,
            'accounting_journal_id'
        );
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(
            FiscalYear::class,
            'fiscal_year_id'
        );
    }

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(
            AccountingPeriod::class,
            'accounting_period_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Audit
    |--------------------------------------------------------------------------
    */

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}