<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_prices', function (Blueprint $table) {
            $table->dropForeign([
                'reseller_id',
            ]);

            $table->dropForeign([
                'product_id',
            ]);

            $table->dropUnique(
                'reseller_prices_reseller_id_product_id_unique'
            );

            $table->index(
                [
                    'reseller_id',
                    'product_id',
                ],
                'reseller_prices_reseller_product_idx'
            );

            $table->foreign('reseller_id')
                ->references('id')
                ->on('resellers')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reseller_prices', function (Blueprint $table) {
            $table->dropForeign([
                'reseller_id',
            ]);

            $table->dropForeign([
                'product_id',
            ]);

            $table->dropIndex(
                'reseller_prices_reseller_product_idx'
            );

            $table->unique(
                [
                    'reseller_id',
                    'product_id',
                ],
                'reseller_prices_reseller_id_product_id_unique'
            );

            $table->foreign('reseller_id')
                ->references('id')
                ->on('resellers')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });
    }
};