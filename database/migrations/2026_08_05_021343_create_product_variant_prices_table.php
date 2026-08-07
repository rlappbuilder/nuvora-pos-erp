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
        Schema::create('product_variant_prices', function (Blueprint $table) {

            $table->id();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('price_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal(
                'last_purchase_price',
                18,
                2
            )->default(0);

            $table->decimal(
                'selling_price',
                18,
                2
            );

            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->text('description')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unique(
                [
                    'branch_id',
                    'product_variant_id',
                    'unit_id',
                    'price_type_id',
                    'effective_from',
                ],
                'product_variant_prices_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'product_variant_prices'
        );
    }
};