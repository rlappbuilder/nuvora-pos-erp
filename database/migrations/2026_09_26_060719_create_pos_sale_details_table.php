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
     Schema::create('pos_sale_details', function (Blueprint $table) {
        $table->id();

        $table->foreignId('pos_sale_id')
            ->constrained('pos_sales')
            ->cascadeOnDelete();

        $table->foreignId('product_variant_id')
            ->constrained('product_variants')
            ->restrictOnDelete();

        $table->foreignId('unit_id')
            ->constrained('units')
            ->restrictOnDelete();

        $table->foreignId('price_type_id')
            ->constrained('price_types')
            ->restrictOnDelete();

        $table->decimal('qty', 15, 6);

        $table->decimal('unit_price', 15, 2);

        $table->decimal('discount_amount', 15, 2)->default(0);

        $table->decimal('subtotal', 15, 2);

        $table->decimal('unit_cost', 15, 2)->default(0);

        $table->decimal('total_cost', 15, 2)->default(0);

        $table->timestamps();

        $table->index('pos_sale_id');
        $table->index('product_variant_id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sale_details');
    }
};
