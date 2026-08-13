<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Core\DocumentActivity;
use App\Models\Company\Company;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\User;

class InventoryAdjustmentHeader extends Model
{
    use HasFactory;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Identity
        |--------------------------------------------------------------------------
        */

        'company_id',

        'branch_id',

        'warehouse_id',


        /*
        |--------------------------------------------------------------------------
        | Document
        |--------------------------------------------------------------------------
        */

        'number',

        'transaction_date',

        'status',


        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        'description',


        /*
        |--------------------------------------------------------------------------
        | Posting
        |--------------------------------------------------------------------------
        */

        'posted_at',

        'posted_by',


        /*
        |--------------------------------------------------------------------------
        | Rejection
        |--------------------------------------------------------------------------
        */

        'rejected_at',

        'rejected_by',

        'rejected_reason',


        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        'created_by',

        'updated_by',

        'deleted_by',

        'deleted_reason',

    ];


    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        'transaction_date' => 'date',

        'posted_at' => 'datetime',

        'rejected_at' => 'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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


    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }


    public function details(): HasMany
    {
        return $this->hasMany(
            InventoryAdjustmentDetail::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Created / Updated By
    |--------------------------------------------------------------------------
    */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public function updater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Posted By
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
    | Rejected By
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
    | Deleted By
    |--------------------------------------------------------------------------
    */

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'deleted_by'
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
    public function movements()
    {
        return $this->hasMany(
            InventoryMovement::class,
            'reference_id'
        )->where(
            'reference_type',
            'INVENTORY_ADJUSTMENT'
        );
    }
}