<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(

            'inventory_adjustment_details',

            function (

                Blueprint $table

            ) {

                $table->decimal(

                    'unit_cost',

                    15,
                    2

                )

                ->default(0)

                ->after(
                    'difference_qty'
                );

            }

        );
    }

    public function down(): void
    {
        Schema::table(

            'inventory_adjustment_details',

            function (

                Blueprint $table

            ) {

                $table->dropColumn(
                    'unit_cost'
                );

            }

        );
    }
};