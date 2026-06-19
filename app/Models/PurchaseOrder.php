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
        'submitted_at',
        'submitted_by',

        'approved_at',
        'approved_by',

        'rejected_at',
        'rejected_by',

        'rejection_reason',

        'cancelled_at',
        'cancelled_by',

        'cancel_reason',

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
    public function submittedBy()
{
    return $this->belongsTo(
        User::class,
        'submitted_by'
    );
}

public function approvedBy()
{
    return $this->belongsTo(
        User::class,
        'approved_by'
    );
}

public function rejectedBy()
{
    return $this->belongsTo(
        User::class,
        'rejected_by'
    );
}

public function cancelledBy()
{
    return $this->belongsTo(
        User::class,
        'cancelled_by'
    );
}
}