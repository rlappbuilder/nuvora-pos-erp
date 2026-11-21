<?php

namespace App\Models\Production\ProductionOrder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Production\ProductionBom;
use App\Models\Production\ProductionBomVersion;
use App\Models\Core\DocumentActivity;
use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\User;

class ProductionOrderHeader extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Fillable
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        // Organization
        'company_id',
        'branch_id',

        // Document
        'number',

        // Production BOM
        'production_bom_id',
        'production_bom_version_id',

        // Warehouse
        'source_warehouse_id',
        'destination_warehouse_id',

        // Production Dates
        'order_date',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',

        // Workflow Status
        'status',

        // Submission
        'submitted_at',
        'submitted_by',

        // Approval
        'approved_at',
        'approved_by',

        // Rejection
        'rejected_at',
        'rejected_by',
        'rejected_reason',

        // Cancellation
        'cancelled_at',
        'cancelled_by',
        'cancelled_reason',

        // Information
        'description',

        // Audit
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
        // Dates
        'order_date' => 'date',
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',

        // Workflow timestamps
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Branch
    |--------------------------------------------------------------------------
    */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Production BOM
    |--------------------------------------------------------------------------
    */

    public function productionBom(): BelongsTo
    {
        return $this->belongsTo(
            ProductionBom::class,
            'production_bom_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Production BOM Version
    |--------------------------------------------------------------------------
    */

    public function productionBomVersion(): BelongsTo
    {
        return $this->belongsTo(
            ProductionBomVersion::class,
            'production_bom_version_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Source Warehouse
    |--------------------------------------------------------------------------
    */

    public function sourceWarehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class,
            'source_warehouse_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Destination Warehouse
    |--------------------------------------------------------------------------
    */

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class,
            'destination_warehouse_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Output Detail
    |--------------------------------------------------------------------------
    */

    public function details(): HasOne
    {
        return $this->hasOne(
            ProductionOrderDetail::class,
            'production_order_header_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Material Snapshots
    |--------------------------------------------------------------------------
    */

    public function materials(): HasMany
    {
        return $this->hasMany(
            ProductionOrderMaterial::class,
            'production_order_header_id'
        )->orderBy('sequence');
    }

    /*
    |--------------------------------------------------------------------------
    | Operation Snapshots
    |--------------------------------------------------------------------------
    */

    public function operations(): HasMany
    {
        return $this->hasMany(
            ProductionOrderOperation::class,
            'production_order_header_id'
        )->orderBy('sequence');
    }

    /*
    |--------------------------------------------------------------------------
    | Production Results
    |--------------------------------------------------------------------------
    */

    public function results(): HasMany
    {
        return $this->hasMany(
            ProductionResult::class,
            'production_order_header_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Created By
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
    | Updated By
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
    | Submitted By
    |--------------------------------------------------------------------------
    */

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'submitted_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Approved By
    |--------------------------------------------------------------------------
    */

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
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
    | Cancelled By
    |--------------------------------------------------------------------------
    */

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
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
            ->orderBy('performed_at', 'asc');
    }
}