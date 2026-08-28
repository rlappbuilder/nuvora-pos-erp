<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\MasterData\Company;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Supplier;
use App\Models\MasterData\Warehouse;
use App\Models\Purchasing\PurchaseOrderHeader;
use App\Models\Purchasing\GoodsReceiptHeader;
use App\Models\Inventory\InventoryMovement;
use App\Models\Core\DocumentActivity;
use App\Models\User;

class PurchaseReturnHeader extends Model
{
    use HasFactory;
    use SoftDeletes;


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

        'purchase_order_id',

        'goods_receipt_id',

        'supplier_id',

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
    | Purchase Order
    |--------------------------------------------------------------------------
    */

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrderHeader::class,
            'purchase_order_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Goods Receipt
    |--------------------------------------------------------------------------
    */

    public function goodsReceipt()
    {
        return $this->belongsTo(
            GoodsReceiptHeader::class,
            'goods_receipt_id'
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
    | Details
    |--------------------------------------------------------------------------
    */

    public function details()
    {
        return $this->hasMany(
            PurchaseReturnDetail::class,
            'purchase_return_header_id'
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
            'PURCHASE_RETURN'
        );
    }

}