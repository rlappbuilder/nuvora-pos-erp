<?php

namespace App\Models;
use App\Models\GoodsReceipt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'grn_number',

        'purchase_order_id',

        'supplier_id',

        'warehouse_id',

        'receipt_date',

        'supplier_do_number',

        'status',

        'remarks',

        'created_by',

        'updated_by',
        'posted_at',
        'posted_by',

        'cancelled_at',
        'cancelled_by',

        'cancel_reason',

    ];

  public function purchaseOrder()
{
    return $this->belongsTo(
        PurchaseOrder::class,
        'purchase_order_id'
    );
}

    public function supplier()
    {
        return $this->belongsTo(
            Supplier::class
        );
    }

    public function warehouse()
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }

public function details()
{
    return $this->hasMany(
        GoodsReceiptDetail::class
    );
}

public function creator()
{
    return $this->belongsTo(
        User::class,
        'created_by'
    );
}

public function poster()
{
    return $this->belongsTo(
        User::class,
        'posted_by'
    );
}

public function canceller()
{
    return $this->belongsTo(
        User::class,
        'cancelled_by'
    );
}
}