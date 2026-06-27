<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(

            'stock_issue_details',

            function (

                Blueprint $table

            ) {

                $table->id();

                $table->foreignId(

                    'stock_issue_id'

                )

                ->constrained()

                ->cascadeOnDelete();

                $table->foreignId(

                    'product_id'

                )

                ->constrained()

                ->cascadeOnUpdate();

                $table->decimal(

                    'qty',

                    18,

                    2

                );

                $table->decimal(

                    'unit_cost',

                    18,

                    2

                );

                $table->decimal(

                    'total_cost',

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

    public function down(): void
    {
        Schema::dropIfExists(

            'stock_issue_details'

        );
    }
};