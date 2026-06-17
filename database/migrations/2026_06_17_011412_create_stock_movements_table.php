<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'stock_movements',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId('product_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('warehouse_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string(
                    'transaction_type',
                    50
                );

                $table->string(
                    'reference_no',
                    100
                )->nullable();

                $table->decimal(
                    'qty',
                    18,
                    2
                );

                $table->decimal(
                    'unit_cost',
                    18,
                    2
                )->default(0);

                $table->text(
                    'remarks'
                )->nullable();

                $table->unsignedBigInteger(
                    'created_by'
                )->nullable();

                $table->timestamps();

            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'stock_movements'
        );
    }
};