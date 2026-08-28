<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_return_details', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_return_header_id')
                ->constrained('purchase_return_headers')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Goods Receipt Detail
            |--------------------------------------------------------------------------
            */

            $table->foreignId('goods_receipt_detail_id')
                ->constrained('goods_receipt_details')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Purchase Order Detail
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_order_detail_id')
                ->constrained('purchase_order_details')
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
            | Quantity
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'received_qty',
                18,
                2
            );

            $table->decimal(
                'returned_qty',
                18,
                2
            );

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
            | Remarks
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')
                ->nullable();

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
                'purchase_return_header_id'
            );

            $table->index(
                'goods_receipt_detail_id'
            );

            $table->index(
                'purchase_order_detail_id'
            );

            $table->index([
                'product_variant_id',
                'unit_id',
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'purchase_return_details'
        );
    }
};