<?php

namespace App\Models\Inventory;
use App\Models\MasterData\Product;
use App\Models\MasterData\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [

        'transfer_number',

        'from_warehouse_id',

        'to_warehouse_id',

        'transfer_date',

        'status',

        'remarks',

        'created_by',

        'updated_by',

        'posted_by',

        'posted_at',

        'completed_by',

        'completed_at',

        'cancelled_by',

        'cancelled_at',

        'cancel_reason',

    ];

    public function fromWarehouse()
    {
        return $this->belongsTo(

            Warehouse::class,

            'from_warehouse_id'

        );
    }

    public function toWarehouse()
    {
        return $this->belongsTo(

            Warehouse::class,

            'to_warehouse_id'

        );
    }

    public function details()
    {
        return $this->hasMany(

            StockTransferDetail::class

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
        public function completer()
    {
        return $this->belongsTo(

            User::class,

            'completed_by'

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