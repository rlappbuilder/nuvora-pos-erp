<?php

namespace App\Models;

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

    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class
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
}