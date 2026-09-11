<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_price_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reseller_id')
                ->constrained('resellers')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->decimal('price', 18, 2);

            $table->date('effective_from');

            $table->date('effective_to')
                ->nullable();

            $table->unsignedBigInteger('created_by')
                ->nullable();

            $table->timestamps();

            $table->index(
                [
                    'reseller_id',
                    'product_id',
                    'effective_from',
                ],
                'rph_reseller_product_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_price_histories');
    }
};