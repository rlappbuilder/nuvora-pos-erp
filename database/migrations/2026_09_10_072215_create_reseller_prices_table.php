<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_prices', function (Blueprint $table) {

            $table->id();

            $table->foreignId('reseller_id')
                ->constrained('resellers')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->decimal('price', 18, 2)
                ->default(0);

            $table->timestamps();

            $table->unique([
                'reseller_id',
                'product_id',
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reseller_prices');
    }
};