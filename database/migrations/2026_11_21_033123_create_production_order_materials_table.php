<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_materials', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Production Order Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId('production_order_header_id')
                ->constrained('production_order_headers')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Source BOM Material
            |--------------------------------------------------------------------------
            */

            $table->foreignId('production_bom_material_id')
                ->constrained('production_bom_materials')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Production Order Operation Snapshot
            |--------------------------------------------------------------------------
            */

            $table->foreignId('production_order_operation_id')
                ->nullable()
                ->constrained('production_order_operations')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Material Component
            |--------------------------------------------------------------------------
            */

            $table->foreignId('component_variant_id')
                ->constrained('product_variants')
                ->restrictOnDelete();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | BOM Quantity Snapshot
            |--------------------------------------------------------------------------
            */

            $table->decimal('bom_qty', 18, 4);

            $table->decimal('scrap_percent', 8, 4)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Production Requirement
            |--------------------------------------------------------------------------
            */

            $table->decimal('required_quantity', 18, 6);

            /*
            |--------------------------------------------------------------------------
            | Material Issue & Return Summary
            |--------------------------------------------------------------------------
            */

            $table->decimal('issued_quantity', 18, 6)
                ->default(0);

            $table->decimal('returned_quantity', 18, 6)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Sequence & Notes
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('sequence')
                ->default(0);

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['production_order_header_id', 'production_bom_material_id'],
                'production_order_materials_bom_unique'
            );

            $table->index(
                ['production_order_header_id', 'sequence'],
                'production_order_materials_sequence_index'
            );

            $table->index(
                'component_variant_id',
                'production_order_materials_component_index'
            );

            $table->index(
                'production_order_operation_id',
                'production_order_materials_operation_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_materials');
    }
};