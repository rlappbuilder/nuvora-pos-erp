<?php

namespace App\Models\Inventory;
use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Model;

class StockIssueDetail extends Model
{
    protected $fillable = [

        'stock_issue_id',

        'product_id',

        'qty',

        'unit_cost',

        'total_cost',

        'remarks',

    ];

    public function stockIssue()
    {
        return $this->belongsTo(

            StockIssue::class

        );
    }

    public function product()
    {
        return $this->belongsTo(

            Product::class

        );
    }
}