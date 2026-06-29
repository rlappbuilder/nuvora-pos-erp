<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MasterData\Product;
use App\Models\MasterData\Supplier;

use App\Models\MasterData\Warehouse;
use App\Models\User;
class GoodsReceiptDetail extends Model
{
    use HasFactory;

    protected $fillable = [

        'goods_receipt_id',

        'product_id',

        'qty_received',

        'unit_cost',

        'line_total',

    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(
            GoodsReceipt::class
        );
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class
        );
    }
}