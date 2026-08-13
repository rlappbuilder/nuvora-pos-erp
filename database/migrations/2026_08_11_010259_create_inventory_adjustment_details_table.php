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
            function (Blueprint $table) {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Header
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'inventory_adjustment_header_id'
                );

                $table->foreign(
                    'inventory_adjustment_header_id',
                    'iad_header_fk'
                )
                    ->references('id')
                    ->on('inventory_adjustment_headers')
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Inventory
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'product_variant_id'
                )
                    ->constrained(
                        'product_variants'
                    )
                    ->restrictOnDelete();

                $table->foreignId(
                    'unit_id'
                )
                    ->constrained(
                        'units'
                    )
                    ->restrictOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Quantity
                |--------------------------------------------------------------------------
                */

                $table->decimal(
                    'system_qty',
                    18,
                    2
                )->default(0);

                $table->decimal(
                    'actual_qty',
                    18,
                    2
                )->default(0);

                $table->decimal(
                    'difference_qty',
                    18,
                    2
                )->default(0);


                /*
                |--------------------------------------------------------------------------
                | Cost
                |--------------------------------------------------------------------------
                */

                $table->decimal(
                    'unit_cost',
                    18,
                    2
                )->default(0);

                $table->decimal(
                    'total_cost',
                    18,
                    2
                )->default(0);


                /*
                |--------------------------------------------------------------------------
                | Information
                |--------------------------------------------------------------------------
                */

                $table->text(
                    'description'
                )->nullable();


                /*
                |--------------------------------------------------------------------------
                | Audit
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'created_by'
                )->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId(
                    'updated_by'
                )->nullable()
                    ->constrained('users')
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Timestamps
                |--------------------------------------------------------------------------
                */

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