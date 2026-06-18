<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;
use App\Models\Warehouse;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [

        'product_id',

        'warehouse_id',

        'transaction_type',

        'reference_no',

        'qty',

        'unit_cost',

        'remarks',

        'created_by',

    ];

    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }

    public function warehouse()
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }
}