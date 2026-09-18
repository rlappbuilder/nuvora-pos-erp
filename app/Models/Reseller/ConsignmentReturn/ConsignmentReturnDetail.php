<?php

namespace App\Models\Reseller\ConsignmentReturn;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Product\ProductVariant;
use App\Models\MasterData\Unit;

class ConsignmentReturnDetail extends Model
{
    use HasFactory;


    protected $table = 'consignment_return_details';


    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        'consignment_return_header_id',


        /*
        |--------------------------------------------------------------------------
        | Product
        |--------------------------------------------------------------------------
        */

        'product_variant_id',

        'unit_id',


        /*
        |--------------------------------------------------------------------------
        | Quantity
        |--------------------------------------------------------------------------
        */

        'returned_qty',


        /*
        |--------------------------------------------------------------------------
        | Cost
        |--------------------------------------------------------------------------
        */

        'unit_cost',

        'total_cost',


        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        'remarks',

    ];


    protected $casts = [

        'returned_qty' =>
            'decimal:6',

        'unit_cost' =>
            'decimal:2',

        'total_cost' =>
            'decimal:2',

    ];


    /*
    |--------------------------------------------------------------------------
    | Consignment Return
    |--------------------------------------------------------------------------
    */

    public function consignmentReturn()
    {
        return $this->belongsTo(
            ConsignmentReturnHeader::class,
            'consignment_return_header_id'
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
            Unit::class,
            'unit_id'
        );
    }

}