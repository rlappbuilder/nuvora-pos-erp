<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'inventory_movements',
            function (Blueprint $table) {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Identity
                |--------------------------------------------------------------------------
                */

                $table->foreignId('company_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('branch_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('warehouse_id')
                    ->constrained()
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Inventory
                |--------------------------------------------------------------------------
                */

                $table->foreignId('product_variant_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('unit_id')
                    ->constrained()
                    ->cascadeOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Reference
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'reference_type',
                    50
                );

                $table->unsignedBigInteger(
                    'reference_id'
                );

                $table->string(
                    'reference_number',
                    100
                );

                /*
                |--------------------------------------------------------------------------
                | Movement
                |--------------------------------------------------------------------------
                */

                $table->decimal(
                    'qty_in',
                    18,
                    2
                )->default(0);

                $table->decimal(
                    'qty_out',
                    18,
                    2
                )->default(0);

                $table->decimal(
                    'balance_qty',
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

                $table->timestamp(
                    'transaction_date'
                );

                $table->text(
                    'description'
                )->nullable();

                /*
                |--------------------------------------------------------------------------
                | Audit
                |--------------------------------------------------------------------------
                */

                $table->unsignedBigInteger(
                    'created_by'
                )->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Index
                |--------------------------------------------------------------------------
                */

                $table->index(
                    [
                        'warehouse_id',
                        'product_variant_id',
                    ],
                    'im_stock_idx'
                );

                $table->index(
                    [
                        'reference_type',
                        'reference_id',
                    ],
                    'im_ref_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'inventory_movements'
        );
    }
};