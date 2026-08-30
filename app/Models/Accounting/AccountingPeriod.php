<?php

namespace App\Models\Accounting;

use App\Models\MasterData\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Accounting Period Model
 *
 * Represents an accounting period within a fiscal year.
 */
class AccountingPeriod extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'accounting_periods';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        'company_id',


        /*
        |--------------------------------------------------------------------------
        | Fiscal Year
        |--------------------------------------------------------------------------
        */

        'fiscal_year_id',


        /*
        |--------------------------------------------------------------------------
        | Period
        |--------------------------------------------------------------------------
        */

        'period_number',

        'name',

        'start_date',

        'end_date',


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        'status',


        /*
        |--------------------------------------------------------------------------
        | Closing
        |--------------------------------------------------------------------------
        */

        'closed_at',

        'closed_by',


        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        'description',


        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

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

        'period_number' =>
            'integer',

        'start_date' =>
            'date',

        'end_date' =>
            'date',

        'closed_at' =>
            'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(
            Company::class
        );
    }


    /**
     * Fiscal Year.
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(
            FiscalYear::class,
            'fiscal_year_id'
        );
    }


    /**
     * Closed By.
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'closed_by'
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
     * Only open periods.
     */
    public function scopeOpen(
        Builder $query
    ): Builder {

        return $query->where(
            'status',
            'Open'
        );

    }


    /**
     * Only closed periods.
     */
    public function scopeClosed(
        Builder $query
    ): Builder {

        return $query->where(
            'status',
            'Closed'
        );

    }


    /**
     * Filter by company.
     */
    public function scopeByCompany(
        Builder $query,
        int $companyId
    ): Builder {

        return $query->where(
            'company_id',
            $companyId
        );

    }


    /**
     * Find the period containing a date.
     */
    public function scopeForDate(
        Builder $query,
        $date
    ): Builder {

        return $query
            ->whereDate(
                'start_date',
                '<=',
                $date
            )
            ->whereDate(
                'end_date',
                '>=',
                $date
            );

    }

}