<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_result_details', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Production Result Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId('production_result_id')
                ->unique()
                ->constrained('production_results')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Output Product
            |--------------------------------------------------------------------------
            */

            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Production Quantity
            |--------------------------------------------------------------------------
            */

            $table->decimal('produced_quantity', 18, 6);

            $table->decimal('accepted_quantity', 18, 6)
                ->default(0);

            $table->decimal('rejected_quantity', 18, 6)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Quality Control
            |--------------------------------------------------------------------------
            */

            $table->foreignId('qc_inspected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('qc_inspected_at')
                ->nullable();

            $table->text('qc_notes')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Information
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                'product_variant_id',
                'production_result_details_product_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_result_details');
    }
};