<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('purchase_order_details', function (Blueprint $table) {

    $table->id();

    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    $table->foreignId('purchase_order_id')
        ->constrained('purchase_order_headers')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();


    /*
    |--------------------------------------------------------------------------
    | Product
    |--------------------------------------------------------------------------
    */

    $table->foreignId('product_variant_id')
        ->constrained('product_variants')
        ->cascadeOnUpdate()
        ->restrictOnDelete();

    $table->foreignId('unit_id')
        ->constrained('units')
        ->cascadeOnUpdate()
        ->restrictOnDelete();


    /*
    |--------------------------------------------------------------------------
    | Quantity
    |--------------------------------------------------------------------------
    */

    $table->decimal('qty', 18, 6)
        ->default(0);

    $table->decimal('received_qty', 18, 6)
        ->default(0);

    $table->decimal('remaining_qty', 18, 6)
        ->default(0);


    /*
    |--------------------------------------------------------------------------
    | Pricing
    |--------------------------------------------------------------------------
    */

    $table->decimal('unit_price', 18, 2)
        ->default(0);

    $table->decimal('discount_rate', 8, 4)
        ->default(0);

    $table->decimal('discount_amount', 18, 2)
        ->default(0);

    $table->decimal('tax_rate', 8, 4)
        ->default(0);

    $table->decimal('tax_amount', 18, 2)
        ->default(0);

    $table->decimal('subtotal', 18, 2)
        ->default(0);

    $table->decimal('total', 18, 2)
        ->default(0);


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

    $table->index('purchase_order_id');

    $table->index('product_variant_id');

});
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_details');
    }
};