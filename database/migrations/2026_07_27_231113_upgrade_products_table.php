<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {

            // Unit Relation
            $table->foreignId('unit_id')
                ->nullable()
                ->after('brand_id')
                ->constrained()
                ->restrictOnDelete();

            // Internal Code
            $table->string('code', 30)
                ->nullable()
                ->unique()
                ->after('unit_id');

            // SEO Slug
            $table->string('slug')
                ->nullable()
                ->unique()
                ->after('sku');

            // Inventory
            $table->boolean('track_stock')
                ->default(true)
                ->after('product_type');

            // Sales
            $table->boolean('is_sellable')
                ->default(true)
                ->after('track_stock');

            // Purchasing
            $table->boolean('is_purchasable')
                ->default(true)
                ->after('is_sellable');

            // Status
            $table->boolean('is_active')
                ->default(true)
                ->after('status');

            // Audit
            $table->unsignedBigInteger('deleted_by')
                ->nullable()
                ->after('updated_by');

        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropForeign(['unit_id']);

            $table->dropColumn([
                'unit_id',
                'code',
                'slug',
                'track_stock',
                'is_sellable',
                'is_purchasable',
                'is_active',
                'deleted_by',
            ]);

        });
    }
};