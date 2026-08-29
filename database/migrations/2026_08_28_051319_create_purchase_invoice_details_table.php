<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoice_details', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_invoice_header_id')
                ->constrained('purchase_invoice_headers')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | 3-Way Matching
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_order_detail_id')
                ->constrained('purchase_order_details')
                ->restrictOnDelete();

            $table->foreignId('goods_receipt_detail_id')
                ->constrained('goods_receipt_details')
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
                20,
                6
            )->default(0);

            $table->decimal(
                'received_qty',
                20,
                6
            )->default(0);

            $table->decimal(
                'invoiced_qty',
                20,
                6
            )->default(0);

            /*
            |--------------------------------------------------------------------------
            | Price
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'unit_price',
                20,
                2
            )->default(0);

            $table->decimal(
                'discount_amount',
                20,
                2
            )->default(0);

            $table->decimal(
                'tax_amount',
                20,
                2
            )->default(0);

            $table->decimal(
                'subtotal',
                20,
                2
            )->default(0);

            $table->decimal(
                'total_amount',
                20,
                2
            )->default(0);

            /*
            |--------------------------------------------------------------------------
            | Notes
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')
                ->nullable();

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
                    'purchase_invoice_header_id',
                    'product_variant_id',
                ],
                'pinv_det_variant_idx'
            );
           $table->index(
                [
                    'purchase_order_detail_id',
                    'goods_receipt_detail_id',
                ],
                'pinv_det_match_idx'
            );

            $table->index('product_variant_id');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'purchase_invoice_details'
        );
    }
};