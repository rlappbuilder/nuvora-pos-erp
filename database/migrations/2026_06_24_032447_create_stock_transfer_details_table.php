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

        'stock_transfer_details',

        function (

            Blueprint $table

        ) {

            $table->id();

            $table->foreignId(
                'stock_transfer_id'
            );

            $table->foreignId(
                'product_id'
            );

            $table->decimal(
                'qty',
                15,
                2
            );

            $table->decimal(
                'unit_cost',
                15,
                2
            )->default(0);

            $table->decimal(
                'total_cost',
                15,
                2
            )->default(0);

            $table->text(
                'remarks'
            )->nullable();

            $table->timestamps();

        }

    );
}

public function down(): void
{
    Schema::dropIfExists(
        'stock_transfer_details'
    );
}
};
