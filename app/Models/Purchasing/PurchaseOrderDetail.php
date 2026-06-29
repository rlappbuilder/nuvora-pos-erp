<?php

namespace App\Models\Purchasing;
use App\Models\MasterData\Product;
use App\Models\MasterData\Supplier;

use App\Models\MasterData\Warehouse;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrderDetail extends Model
{
    use HasFactory;

    protected $fillable = [

        'purchase_order_id',

        'product_id',

        'qty',

        'unit_cost',

        'discount',

        'tax',

        'line_total',

    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(
            PurchaseOrder::class
        );
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }
}