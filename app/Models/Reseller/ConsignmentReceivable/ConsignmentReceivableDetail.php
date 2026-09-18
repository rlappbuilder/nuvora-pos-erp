<?php

namespace App\Models\Reseller\ConsignmentReceivable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Reseller\ConsignmentSettlement\ConsignmentSettlementHeader;

class ConsignmentReceivableDetail extends Model
{
    use HasFactory;

    protected $table = 'consignment_receivable_details';

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        'consignment_receivable_header_id',

        /*
        |--------------------------------------------------------------------------
        | Consignment Settlement
        |--------------------------------------------------------------------------
        */

        'settlement_header_id',

        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        */

        'settlement_amount',

        'previous_paid_amount',

        'previous_outstanding_amount',

        'payment_amount',

        /*
        |--------------------------------------------------------------------------
        | Notes
        |--------------------------------------------------------------------------
        */

        'remarks',

    ];

    protected $casts = [

        'settlement_amount' =>
            'decimal:2',

        'previous_paid_amount' =>
            'decimal:2',

        'previous_outstanding_amount' =>
            'decimal:2',

        'payment_amount' =>
            'decimal:2',

    ];


    /*
    |--------------------------------------------------------------------------
    | Consignment Receivable
    |--------------------------------------------------------------------------
    */

    public function consignmentReceivable()
    {
        return $this->belongsTo(
            ConsignmentReceivableHeader::class,
            'consignment_receivable_header_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Consignment Settlement
    |--------------------------------------------------------------------------
    */

    public function settlement()
    {
        return $this->belongsTo(
            ConsignmentSettlementHeader::class,
            'settlement_header_id'
        );
    }
}