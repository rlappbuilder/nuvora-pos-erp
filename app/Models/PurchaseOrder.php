<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [

        'po_number',

        'supplier_id',

        'warehouse_id',

        'order_date',

        'expected_date',

        'status',

        'remarks',

        'subtotal',

        'tax_amount',

        'discount_amount',

        'grand_total',

        'created_by',

        'updated_by',

    ];

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
            PurchaseOrderDetail::class
        );
    }
     public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()

            ->logFillable()

            ->logOnlyDirty()

            ->dontSubmitEmptyLogs();
    }
}