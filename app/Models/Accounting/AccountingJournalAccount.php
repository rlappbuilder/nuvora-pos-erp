<?php

namespace App\Models\Accounting;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Accounting\ChartOfAccount;
class AccountingJournalAccount extends Model
{
    use HasFactory, SoftDeletes;


    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table =
        'accounting_journal_accounts';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'accounting_journal_id',

        'account_id',

        'role',

        'is_default',

        'created_by',

        'updated_by',

        'deleted_by',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'is_default' => 'boolean',

    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Accounting Journal.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(
            AccountingJournal::class,
            'accounting_journal_id'
        );
    }


    /**
     * Chart of Account.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(
            ChartOfAccount::class,
            'account_id'
        );
    }


    /**
     * Created By.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    /**
     * Updated By.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    /**
     * Deleted By.
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'deleted_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Filter by journal.
     */
    public function scopeForJournal(
        Builder $query,
        int $journalId
    ): Builder {

        return $query->where(
            'accounting_journal_id',
            $journalId
        );
    }


    /**
     * Filter by role.
     */
    public function scopeRole(
        Builder $query,
        string $role
    ): Builder {

        return $query->where(
            'role',
            $role
        );
    }


    /**
     * Only default accounts.
     */
    public function scopeDefault(
        Builder $query
    ): Builder {

        return $query->where(
            'is_default',
            true
        );
    }


}