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
        Schema::create('settlement_details', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId('settlement_header_id')
                ->constrained('settlement_headers')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnUpdate();

            $table->foreignId('unit_id')
                ->constrained()
                ->cascadeOnUpdate();

            /*
            |--------------------------------------------------------------------------
            | Settlement Quantity & Price
            |--------------------------------------------------------------------------
            */

            $table->decimal('qty_sold', 18, 2);

            $table->decimal('unit_price', 18, 2);

            $table->decimal('total_amount', 18, 2);

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'settlement_header_id',
                'product_variant_id',
            ]);

            $table->index('product_variant_id');

            $table->index('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlement_details');
    }
};