<?php

namespace App\Models\Inventory;
use App\Models\MasterData\Product;
use App\Models\MasterData\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [

        'adjustment_number',

        'warehouse_id',

        'adjustment_date',

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

    public function warehouse()
    {
        return $this->belongsTo(

            Warehouse::class

        );
    }

    public function details()
    {
        return $this->hasMany(

            InventoryAdjustmentDetail::class

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