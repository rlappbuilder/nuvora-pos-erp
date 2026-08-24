<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Core\DocumentActivity;
use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Supplier;
use App\Models\User;

class PurchaseOrderHeader extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Identity
        |--------------------------------------------------------------------------
        */

        'company_id',

        'branch_id',

        'warehouse_id',

        'supplier_id',

        'purchase_request_id',

        /*
        |--------------------------------------------------------------------------
        | Document
        |--------------------------------------------------------------------------
        */

        'number',

        'order_date',

        'required_date',

        'status',

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

        'rejected_reason',

        /*
        |--------------------------------------------------------------------------
        | Supplier Communication
        |--------------------------------------------------------------------------
        */

        'sent_at',

        'sent_by',

        'confirmed_at',

        'confirmed_by',

        /*
        |--------------------------------------------------------------------------
        | Cancellation
        |--------------------------------------------------------------------------
        */

        'cancelled_at',

        'cancelled_by',

        'cancelled_reason',

        /*
        |--------------------------------------------------------------------------
        | Receiving Summary
        |--------------------------------------------------------------------------
        */

        'total_quantity',

        'received_quantity',

        'remaining_quantity',

        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        */

        'subtotal',

        'discount_amount',

        'tax_amount',

        'grand_total',

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

        'deleted_reason',

    ];

    protected $casts = [

        'order_date' =>
            'date',

        'required_date' =>
            'date',

        'approved_at' =>
            'datetime',

        'rejected_at' =>
            'datetime',

        'sent_at' =>
            'datetime',

        'confirmed_at' =>
            'datetime',

        'cancelled_at' =>
            'datetime',

        'total_quantity' =>
            'decimal:6',

        'received_quantity' =>
            'decimal:6',

        'remaining_quantity' =>
            'decimal:6',

        'subtotal' =>
            'decimal:2',

        'discount_amount' =>
            'decimal:2',

        'tax_amount' =>
            'decimal:2',

        'grand_total' =>
            'decimal:2',

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
    | Supplier
    |--------------------------------------------------------------------------
    */

    public function supplier()
    {
        return $this->belongsTo(
            Supplier::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Purchase Request
    |--------------------------------------------------------------------------
    */

    public function purchaseRequest()
    {
        return $this->belongsTo(
            PurchaseRequestHeader::class,
            'purchase_request_id'
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
            PurchaseOrderDetail::class,
            'purchase_order_id'
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
    | Sent By
    |--------------------------------------------------------------------------
    */

    public function sender()
    {
        return $this->belongsTo(
            User::class,
            'sent_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmed By
    |--------------------------------------------------------------------------
    */

    public function confirmer()
    {
        return $this->belongsTo(
            User::class,
            'confirmed_by'
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
    | Deleted By
    |--------------------------------------------------------------------------
    */

    public function deleter()
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