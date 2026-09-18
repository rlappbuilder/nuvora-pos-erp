<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consignment_receivable_details', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'consignment_receivable_header_id'
            );

            /*
            |--------------------------------------------------------------------------
            | Settlement
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'settlement_header_id'
            );

            /*
            |--------------------------------------------------------------------------
            | Amounts
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'settlement_amount',
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
            | Remarks
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign(
                'consignment_receivable_header_id',
                'creceivable_details_header_fk'
            )
                ->references('id')
                ->on('consignment_receivable_headers')
                ->cascadeOnDelete();

            $table->foreign(
                'settlement_header_id',
                'creceivable_details_settlement_fk'
            )
                ->references('id')
                ->on('settlement_headers')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'consignment_receivable_header_id',
                    'settlement_header_id',
                ],
                'creceivable_settlement_unique'
            );

            $table->index(
                'settlement_header_id',
                'creceivable_settlement_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'consignment_receivable_details'
        );
    }
};