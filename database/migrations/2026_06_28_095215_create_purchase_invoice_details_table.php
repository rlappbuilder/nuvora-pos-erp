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

            'purchase_invoice_details',

            function (

                Blueprint $table

            ) {

                $table->id();

                $table->foreignId(

                    'purchase_invoice_id'

                )

                ->constrained()

                ->cascadeOnDelete();

                $table->foreignId(

                    'product_id'

                )

                ->constrained()

                ->cascadeOnDelete();

                $table->foreignId(

                    'goods_receipt_detail_id'

                )

                ->constrained(

                    'goods_receipt_details'

                )

                ->cascadeOnDelete();

               

                $table->decimal(

                    'qty',

                    18,

                    2

                );

                $table->string(

                    'unit'

                );

                $table->decimal(

                    'unit_cost',

                    18,

                    2

                );

                $table->decimal(

                    'discount',

                    18,

                    2

                )

                ->default(

                    0

                );

                $table->decimal(

                    'tax',

                    18,

                    2

                )

                ->default(

                    0

                );

                $table->decimal(

                    'subtotal',

                    18,

                    2

                );

                $table->decimal(

                    'total',

                    18,

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(

            'purchase_invoice_details'

        );
    }
};