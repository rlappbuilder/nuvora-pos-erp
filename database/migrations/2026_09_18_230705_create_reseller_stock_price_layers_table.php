<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reseller_stock_price_layers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Context
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignId('reseller_id')
                ->constrained('resellers')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            */

            $table->foreignId('consignment_out_id')
                ->constrained('consignment_outs')
                ->restrictOnDelete();

            $table->foreignId('consignment_out_detail_id')
                ->constrained('consignment_out_details')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Price Layer
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'unit_price',
                18,
                2
            );

            $table->decimal(
                'original_qty',
                18,
                6
            );

            $table->decimal(
                'remaining_qty',
                18,
                6
            );


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'status',
                [
                    'Open',
                    'Exhausted',
                ]
            )
                ->default('Open');


            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'reseller_id',
                    'product_variant_id',
                    'unit_id',
                    'status',
                ],
                'reseller_stock_price_layers_stock_idx'
            );

            $table->index(
                [
                    'company_id',
                    'branch_id',
                    'warehouse_id',
                    'reseller_id',
                ],
                'reseller_stock_price_layers_context_idx'
            );

            $table->index(
                'consignment_out_id',
                'reseller_stock_price_layers_out_idx'
            );

            $table->index(
                'consignment_out_detail_id',
                'reseller_stock_price_layers_detail_idx'
            );

        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'reseller_stock_price_layers'
        );
    }
};