<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_payment_details', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_payment_header_id')
                ->constrained('purchase_payment_headers')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Purchase Invoice
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_invoice_header_id')
                ->constrained('purchase_invoice_headers')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'invoice_amount',
                20,
                2
            )->default(0);

            $table->decimal(
                'previous_paid_amount',
                20,
                2
            )->default(0);

            $table->decimal(
                'previous_outstanding_amount',
                20,
                2
            )->default(0);

            $table->decimal(
                'payment_amount',
                20,
                2
            )->default(0);

            /*
            |--------------------------------------------------------------------------
            | Notes
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'purchase_payment_header_id',
                'purchase_invoice_header_id',
            ], 'ppayment_invoice_unique');

            $table->index(
                'purchase_invoice_header_id'
            );

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'purchase_payment_details'
        );
    }
};