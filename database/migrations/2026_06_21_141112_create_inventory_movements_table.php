<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create(

        'inventory_movements',

        function (

            Blueprint $table

        ) {

            $table->id();

            $table->foreignId(
                'product_id'
            );

            $table->foreignId(
                'warehouse_id'
            );

            $table->string(
                'reference_type'
            );

            $table->unsignedBigInteger(
                'reference_id'
            );

            $table->string(
                'reference_number'
            );

            $table->decimal(
                'qty_in',
                15,
                2
            )->default(0);

            $table->decimal(
                'qty_out',
                15,
                2
            )->default(0);

            $table->decimal(
                'balance_qty',
                15,
                2
            )->default(0);

            $table->timestamp(
                'transaction_date'
            );

            $table->foreignId(
                'created_by'
            )->nullable();

            $table->timestamps();

        }

    );
}
};
