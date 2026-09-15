<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignment_out_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('consignment_out_id')
                ->constrained('consignment_outs')
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('qty', 18, 2);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('total_price', 18, 2);

            $table->timestamps();

            $table->index(
                ['consignment_out_id', 'product_variant_id'],
                'cod_out_product_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignment_out_details');
    }
};