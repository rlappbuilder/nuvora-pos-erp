<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignment_return_price_layers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Return Detail
            |--------------------------------------------------------------------------
            */

            $table->foreignId('return_detail_id');

            /*
            |--------------------------------------------------------------------------
            | Price Layer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('price_layer_id');

            /*
            |--------------------------------------------------------------------------
            | Allocation
            |--------------------------------------------------------------------------
            */

            $table->decimal('qty', 18, 6);

            $table->decimal('unit_price', 18, 2);

            $table->decimal('total_value', 18, 2);

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign(
                'return_detail_id',
                'creturn_price_layers_detail_fk'
            )
                ->references('id')
                ->on('consignment_return_details')
                ->cascadeOnDelete();


            $table->foreign(
                'price_layer_id',
                'creturn_price_layers_layer_fk'
            )
                ->references('id')
                ->on('reseller_stock_price_layers')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                'return_detail_id',
                'creturn_price_layers_detail_idx'
            );

            $table->index(
                'price_layer_id',
                'creturn_price_layers_layer_idx'
            );


            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Allocation
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'return_detail_id',
                    'price_layer_id',
                ],
                'creturn_detail_layer_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'consignment_return_price_layers'
        );
    }
};