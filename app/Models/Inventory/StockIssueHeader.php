<?php

namespace App\Models\Inventory;

use App\Models\MasterData\Branch;
use App\Models\MasterData\Company;
use App\Models\MasterData\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Core\DocumentActivity;
class StockIssueHeader extends Model
{
    use HasFactory;
    use SoftDeletes;


    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    protected $table = 'stock_issue_headers';


    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        'company_id',

        'branch_id',

        'warehouse_id',

        'number',

        'transaction_date',

        'issue_type',

        'status',

        'description',

        'rejected_reason',

        'rejected_at',

        'rejected_by',

        'posted_at',

        'posted_by',

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

        'transaction_date' =>
            'date',

        'rejected_at' =>
            'datetime',

        'posted_at' =>
            'datetime',

        'deleted_at' =>
            'datetime',

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
    | Warehouse
    |--------------------------------------------------------------------------
    */

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Details
    |--------------------------------------------------------------------------
    */

    public function details(): HasMany
    {
        return $this->hasMany(
            StockIssueDetail::class,
            'stock_issue_header_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Creator
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Updater
    |--------------------------------------------------------------------------
    */

    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Poster
    |--------------------------------------------------------------------------
    */

    public function poster(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'posted_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Rejector
    |--------------------------------------------------------------------------
    */

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'rejected_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Deleter
    |--------------------------------------------------------------------------
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
    | Inventory Movements
    |--------------------------------------------------------------------------
    */

    public function movements(): HasMany
    {
        return $this->hasMany(
            InventoryMovement::class,
            'reference_id'
        )
        ->where(
            'reference_type',
            'STOCK_ISSUE'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Workflow Activities
    |--------------------------------------------------------------------------
    */

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