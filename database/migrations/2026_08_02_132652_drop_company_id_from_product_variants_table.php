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
        Schema::table('product_variants', function (Blueprint $table) {

        $table->dropUnique(
            'product_variants_company_id_sku_unique'
        );

        $table->dropIndex(
            'product_variants_company_id_product_id_index'
        );

        $table->dropColumn('company_id');

    });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            //
        });
    }
};
