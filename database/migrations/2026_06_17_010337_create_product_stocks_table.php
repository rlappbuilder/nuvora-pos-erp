<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'product_stocks',
            function (Blueprint $table) {

                $table->id();

                $table->foreignId('product_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('warehouse_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->decimal(
                    'qty',
                    18,
                    2
                )->default(0);

                $table->unsignedBigInteger(
                    'created_by'
                )->nullable();

                $table->unsignedBigInteger(
                    'updated_by'
                )->nullable();

                $table->timestamps();

                $table->unique([
                    'product_id',
                    'warehouse_id'
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'product_stocks'
        );
    }
};