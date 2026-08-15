<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\User;
use App\Models\Core\DocumentActivity;

class StockTransferHeader extends Model
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

        'from_branch_id',

        'from_warehouse_id',

        'to_branch_id',

        'to_warehouse_id',


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
    | From Location
    |--------------------------------------------------------------------------
    */

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(
            Branch::class,
            'from_branch_id'
        );
    }


    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class,
            'from_warehouse_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | To Location
    |--------------------------------------------------------------------------
    */

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(
            Branch::class,
            'to_branch_id'
        );
    }


    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class,
            'to_warehouse_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Details
    |--------------------------------------------------------------------------
    */

    public function details()
        {
            return $this->hasMany(
                StockTransferDetail::class,
                'stock_transfer_header_id',
                'id'
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
            'STOCK_TRANSFER'
        );
    }
}