<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(

            'inventory_adjustment_details',

            function (

                Blueprint $table

            ) {

                $table->id();

                $table->foreignId(
                    'inventory_adjustment_id'
                );

                $table->foreignId(
                    'product_id'
                );

                $table->decimal(
                    'system_qty',
                    15,
                    2
                );

                $table->decimal(
                    'physical_qty',
                    15,
                    2
                );

                $table->decimal(
                    'difference_qty',
                    15,
                    2
                );

                $table->text(
                    'remarks'
                )
                ->nullable();

                $table->timestamps();

            }

        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'inventory_adjustment_details'
        );
    }
};