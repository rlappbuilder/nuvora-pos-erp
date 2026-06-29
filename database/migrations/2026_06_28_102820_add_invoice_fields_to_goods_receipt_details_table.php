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

            'goods_receipt_details',

            function (

                Blueprint $table

            ) {

                $table->decimal(

                    'invoiced_qty',

                    18,

                    2

                )

                ->default(

                    0

                )

                ->after(

                    'qty_received'

                );

            }

        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(

            'goods_receipt_details',

            function (

                Blueprint $table

            ) {

                $table->dropColumn(

                    'invoiced_qty'

                );

            }

        );
    }
};