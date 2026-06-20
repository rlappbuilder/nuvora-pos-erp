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
    Schema::table(
        'goods_receipts',
        function (
            Blueprint $table
        ) {

            $table->string(
                'grn_number'
            )->unique();

            $table->foreignId(
                'purchase_order_id'
            );

            $table->foreignId(
                'supplier_id'
            );

            $table->foreignId(
                'warehouse_id'
            );

            $table->date(
                'receipt_date'
            );

            $table->string(
                'supplier_do_number'
            );

            $table->string(
                'status'
            )->default(
                'Draft'
            );

            $table->text(
                'remarks'
            )->nullable();

            $table->unsignedBigInteger(
                'created_by'
            )->nullable();

            $table->unsignedBigInteger(
                'updated_by'
            )->nullable();

            $table->softDeletes();

        }
    );
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            //
        });
    }
};
