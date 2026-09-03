<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;
use App\Models\Accounting\ChartOfAccount;

class JournalEntryLine extends Model
{
    protected $table = 'journal_entry_lines';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'line_number',
        'description',
        'debit',
        'credit',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

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
    /*
|--------------------------------------------------------------------------
| General Ledger
|--------------------------------------------------------------------------
*/

public function ledgerEntry(): HasOne
{
    return $this->hasOne(
        GeneralLedger::class,
        'journal_entry_line_id'
    );
}
}