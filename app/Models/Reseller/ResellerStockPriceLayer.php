<?php

namespace App\Models\Reseller;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Company\Company;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Warehouse;
use App\Models\MasterData\Unit;
use App\Models\Product\ProductVariant;
use App\Models\Reseller\ConsignmentOut\ConsignmentOutHeader;
use App\Models\Reseller\ConsignmentOut\ConsignmentOutDetail;

class ResellerStockPriceLayer extends Model
{
    use HasFactory;


    protected $table =
        'reseller_stock_price_layers';


    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Context
        |--------------------------------------------------------------------------
        */

        'company_id',

        'branch_id',

        'warehouse_id',

        'reseller_id',


        /*
        |--------------------------------------------------------------------------
        | Product
        |--------------------------------------------------------------------------
        */

        'product_variant_id',

        'unit_id',


        /*
        |--------------------------------------------------------------------------
        | Source
        |--------------------------------------------------------------------------
        */

        'consignment_out_id',

        'consignment_out_detail_id',


        /*
        |--------------------------------------------------------------------------
        | Price Layer
        |--------------------------------------------------------------------------
        */

        'unit_price',

        'original_qty',

        'remaining_qty',


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        'status',

    ];


    protected $casts = [

        'unit_price' =>
            'decimal:2',

        'original_qty' =>
            'decimal:6',

        'remaining_qty' =>
            'decimal:6',

    ];


    /*
    |--------------------------------------------------------------------------
    | Company
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(
            Company::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Branch
    |--------------------------------------------------------------------------
    */

    public function branch()
    {
        return $this->belongsTo(
            Branch::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Warehouse
    |--------------------------------------------------------------------------
    */

    public function warehouse()
    {
        return $this->belongsTo(
            Warehouse::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reseller
    |--------------------------------------------------------------------------
    */

    public function reseller()
    {
        return $this->belongsTo(
            Reseller::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Product Variant
    |--------------------------------------------------------------------------
    */

    public function variant()
    {
        return $this->belongsTo(
            ProductVariant::class,
            'product_variant_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Unit
    |--------------------------------------------------------------------------
    */

    public function unit()
    {
        return $this->belongsTo(
            Unit::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Consignment Out
    |--------------------------------------------------------------------------
    */

    public function consignmentOut()
    {
        return $this->belongsTo(
            ConsignmentOutHeader::class,
            'consignment_out_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Consignment Out Detail
    |--------------------------------------------------------------------------
    */

    public function consignmentOutDetail()
    {
        return $this->belongsTo(
            ConsignmentOutDetail::class,
            'consignment_out_detail_id'
        );
    }

}