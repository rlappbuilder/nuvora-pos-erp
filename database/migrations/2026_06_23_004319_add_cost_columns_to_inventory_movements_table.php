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
    Schema::table(

        'inventory_movements',

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
                'balance_qty'
            );

            $table->decimal(

                'total_cost',

                18,
                2

            )

            ->default(0)

            ->after(
                'unit_cost'
            );

        }

    );
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            //
        });
    }
};
