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
    Schema::create('stock_opname_details', function (Blueprint $table) {

        $table->id();

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        $table->foreignId('stock_opname_header_id')
            ->constrained('stock_opname_headers')
            ->cascadeOnDelete();

        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        $table->foreignId('product_variant_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('unit_id')
            ->constrained()
            ->cascadeOnDelete();

        /*
        |--------------------------------------------------------------------------
        | Stock
        |--------------------------------------------------------------------------
        */

        $table->decimal(
            'system_qty',
            18,
            2
        )->default(0);

        $table->decimal(
            'actual_qty',
            18,
            2
        )->default(0);

        $table->decimal(
            'difference_qty',
            18,
            2
        )->default(0);

        /*
        |--------------------------------------------------------------------------
        | Cost
        |--------------------------------------------------------------------------
        */

        $table->decimal(
            'unit_cost',
            18,
            2
        )->default(0);

        $table->decimal(
            'difference_cost',
            18,
            2
        )->default(0);

        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        $table->text('description')
            ->nullable();

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        $table->unsignedBigInteger('created_by')
            ->nullable();

        $table->unsignedBigInteger('updated_by')
            ->nullable();

        $table->timestamps();

        /*
        |--------------------------------------------------------------------------
        | Index
        |--------------------------------------------------------------------------
        */

        $table->index(
            [
                'stock_opname_header_id',
                'product_variant_id',
            ],
            'sod_product_idx'
        );

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_details');
    }
};
