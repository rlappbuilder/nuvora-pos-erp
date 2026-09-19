<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'consignment_settlement_price_layers',
            function (Blueprint $table) {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Settlement Detail
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'settlement_detail_id'
                );

                $table->foreign(
                    'settlement_detail_id',
                    'csettle_price_layers_detail_fk'
                )
                    ->references('id')
                    ->on('settlement_details')
                    ->cascadeOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Reseller Stock Price Layer
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'price_layer_id'
                );

                $table->foreign(
                    'price_layer_id',
                    'csettle_price_layers_layer_fk'
                )
                    ->references('id')
                    ->on('reseller_stock_price_layers')
                    ->restrictOnDelete();


                /*
                |--------------------------------------------------------------------------
                | FIFO Allocation
                |--------------------------------------------------------------------------
                */

                $table->decimal(
                    'qty',
                    18,
                    6
                );

                $table->decimal(
                    'unit_price',
                    18,
                    2
                );

                $table->decimal(
                    'total_value',
                    18,
                    2
                );


                /*
                |--------------------------------------------------------------------------
                | Timestamps
                |--------------------------------------------------------------------------
                */

                $table->timestamps();


                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    'settlement_detail_id',
                    'csettle_price_layers_detail_idx'
                );

                $table->index(
                    'price_layer_id',
                    'csettle_price_layers_layer_idx'
                );


                /*
                |--------------------------------------------------------------------------
                | Prevent Duplicate Allocation
                |--------------------------------------------------------------------------
                */

                $table->unique(
                    [
                        'settlement_detail_id',
                        'price_layer_id',
                    ],
                    'csettle_detail_layer_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'consignment_settlement_price_layers'
        );
    }
};