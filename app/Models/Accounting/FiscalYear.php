<?php

namespace App\Models\Accounting;

use App\Models\MasterData\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fiscal Year Model
 *
 * Represents a company's fiscal year.
 */
class FiscalYear extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'fiscal_years';


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

        'year',

        'start_date',

        'end_date',


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        'status',

        'is_closed',


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

        'year' =>
            'integer',

        'start_date' =>
            'date',

        'end_date' =>
            'date',

        'is_closed' =>
            'boolean',

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
     * Accounting Periods.
     */
    public function periods(): HasMany
    {
        return $this->hasMany(
            AccountingPeriod::class,
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
     * Only open fiscal years.
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
     * Only closed fiscal years.
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

}