<?php

namespace App\Models\Purchasing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchasePaymentDetail extends Model
{
    use HasFactory;

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        'purchase_payment_header_id',

        /*
        |--------------------------------------------------------------------------
        | Purchase Invoice
        |--------------------------------------------------------------------------
        */

        'purchase_invoice_header_id',

        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        */

        'invoice_amount',

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

        'invoice_amount' =>
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
    | Purchase Payment
    |--------------------------------------------------------------------------
    */

    public function purchasePayment()
    {
        return $this->belongsTo(
            PurchasePaymentHeader::class,
            'purchase_payment_header_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Purchase Invoice
    |--------------------------------------------------------------------------
    */

    public function purchaseInvoice()
    {
        return $this->belongsTo(
            PurchaseInvoiceHeader::class,
            'purchase_invoice_header_id'
        );
    }
}