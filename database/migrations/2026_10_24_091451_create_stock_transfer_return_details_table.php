<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_return_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_transfer_return_header_id');

            $table->foreign(
                'stock_transfer_return_header_id',
                'strd_return_header_fk'
            )
                ->references('id')
                ->on('stock_transfer_return_headers')
                ->cascadeOnDelete();

            $table->foreignId('stock_transfer_detail_id');

            $table->foreign(
                'stock_transfer_detail_id',
                'strd_transfer_detail_fk'
            )
                ->references('id')
                ->on('stock_transfer_details')
                ->restrictOnDelete();

            $table->foreignId('product_variant_id');

            $table->foreign(
                'product_variant_id',
                'strd_variant_fk'
            )
                ->references('id')
                ->on('product_variants')
                ->restrictOnDelete();

            $table->foreignId('unit_id');

            $table->foreign(
                'unit_id',
                'strd_unit_fk'
            )
                ->references('id')
                ->on('units')
                ->restrictOnDelete();

            $table->decimal('qty', 18, 2)->default(0);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->decimal('total_cost', 18, 2)->default(0);

            $table->text('description')->nullable();

            $table->foreignId('created_by')
                ->nullable();

            $table->foreign(
                'created_by',
                'strd_created_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable();

            $table->foreign(
                'updated_by',
                'strd_updated_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                'stock_transfer_detail_id',
                'strd_transfer_detail_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_return_details');
    }
};