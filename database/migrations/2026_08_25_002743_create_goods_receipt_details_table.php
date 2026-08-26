<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipt_details', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId('goods_receipt_header_id')
                ->constrained('goods_receipts_headers')
                ->cascadeOnDelete();

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
                'ordered_qty',
                18,
                2
            );

            $table->decimal(
                'received_qty',
                18,
                2
            );

            $table->decimal(
                'rejected_qty',
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
                'goods_receipt_header_id'
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
            'goods_receipt_details'
        );
    }
};