<?php

namespace App\Models\Reseller\ConsignmentReturn;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Core\DocumentActivity;
use App\Models\Inventory\InventoryMovement;
use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\Reseller\Reseller;
use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;
use App\Models\User;

class ConsignmentReturnHeader extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $table = 'consignment_return_headers';


    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Company / Branch
        |--------------------------------------------------------------------------
        */

        'company_id',

        'branch_id',


        /*
        |--------------------------------------------------------------------------
        | Document
        |--------------------------------------------------------------------------
        */

        'return_number',

        'settlement_header_id',

        'reseller_id',

        'warehouse_id',

        'return_date',

        'status',


        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        'remarks',


        /*
        |--------------------------------------------------------------------------
        | Submission
        |--------------------------------------------------------------------------
        */

        'submitted_at',

        'submitted_by',


        /*
        |--------------------------------------------------------------------------
        | Approval
        |--------------------------------------------------------------------------
        */

        'approved_at',

        'approved_by',


        /*
        |--------------------------------------------------------------------------
        | Rejection
        |--------------------------------------------------------------------------
        */

        'rejected_at',

        'rejected_by',

        'reject_reason',


        /*
        |--------------------------------------------------------------------------
        | Posting
        |--------------------------------------------------------------------------
        */

        'posted_at',

        'posted_by',


        /*
        |--------------------------------------------------------------------------
        | Cancellation
        |--------------------------------------------------------------------------
        */

        'cancelled_at',

        'cancelled_by',

        'cancel_reason',


        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        'created_by',

        'updated_by',

    ];


    protected $casts = [

        'return_date' =>
            'date',

        'submitted_at' =>
            'datetime',

        'approved_at' =>
            'datetime',

        'rejected_at' =>
            'datetime',

        'posted_at' =>
            'datetime',

        'cancelled_at' =>
            'datetime',

    ];


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    public function company()
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

    public function branch()
    {
        return $this->belongsTo(
            Branch::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reseller
    |--------------------------------------------------------------------------
    */

    public function reseller()
    {
        return $this->belongsTo(
            Reseller::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Warehouse
    |--------------------------------------------------------------------------
    */

    public function warehouse()
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Consignment Settlement
    |--------------------------------------------------------------------------
    */

    public function settlement()
    {
        return $this->belongsTo(
            ConsignmentSettlementHeader::class,
            'settlement_header_id'
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
            ConsignmentReturnDetail::class,
            'consignment_return_header_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Created / Updated By
    |--------------------------------------------------------------------------
    */

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public function updater()
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

    public function submitter()
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

    public function approver()
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

    public function rejector()
    {
        return $this->belongsTo(
            User::class,
            'rejected_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Posted By
    |--------------------------------------------------------------------------
    */

    public function poster()
    {
        return $this->belongsTo(
            User::class,
            'posted_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Cancelled By
    |--------------------------------------------------------------------------
    */

    public function canceller()
    {
        return $this->belongsTo(
            User::class,
            'cancelled_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Document Activities
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


    /*
    |--------------------------------------------------------------------------
    | Inventory Movements
    |--------------------------------------------------------------------------
    */

    public function inventoryMovements()
    {
        return $this->hasMany(
            InventoryMovement::class,
            'reference_id'
        )
        ->where(
            'reference_type',
            'CONSIGNMENT_RETURN'
        );
    }

}