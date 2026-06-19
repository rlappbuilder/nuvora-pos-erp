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
    Schema::create(
        'goods_receipt_details',
        function (
            Blueprint $table
        ) {

            $table->id();

            $table->foreignId(
                'goods_receipt_id'
            );

            $table->foreignId(
                'product_id'
            );

            $table->decimal(
                'qty_received',
                15,
                2
            );

            $table->decimal(
                'unit_cost',
                15,
                2
            );

            $table->decimal(
                'line_total',
                15,
                2
            );

            $table->timestamps();

        }
    );
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_details');
    }
};
