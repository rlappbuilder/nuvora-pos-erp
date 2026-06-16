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
    Schema::create('products', function (Blueprint $table) {

        $table->id();

        $table->foreignId('category_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('brand_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();

        $table->string('sku', 50)
            ->unique();

        $table->string('barcode')
            ->nullable();

        $table->string('name');

        $table->text('description')
            ->nullable();

        $table->string('unit', 50)
            ->default('PCS');

        $table->decimal(
            'cost_price',
            18,
            2
        )->default(0);

        $table->decimal(
            'selling_price',
            18,
            2
        )->default(0);

        $table->integer(
            'minimum_stock'
        )->default(0);

        $table->boolean(
            'status'
        )->default(true);

        $table->unsignedBigInteger(
            'created_by'
        )->nullable();

        $table->unsignedBigInteger(
            'updated_by'
        )->nullable();

        $table->softDeletes();

        $table->timestamps();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
