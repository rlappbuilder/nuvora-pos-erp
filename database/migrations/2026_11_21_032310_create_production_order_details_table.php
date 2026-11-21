<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_operations', function (Blueprint $table) {

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
            | Source BOM Operation
            |--------------------------------------------------------------------------
            */

            $table->foreignId('production_bom_operation_id')
                ->nullable()
                ->constrained('production_bom_operations')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Production Operation
            |--------------------------------------------------------------------------
            */

            $table->foreignId('production_operation_id')
                ->constrained('production_operations')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Routing Snapshot
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('sequence');

            $table->decimal('planned_duration_minutes', 12, 2)
                ->nullable();

            $table->text('instructions')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['production_order_header_id', 'sequence'],
                'production_order_operations_sequence_unique'
            );

            $table->unique(
                ['production_order_header_id', 'production_bom_operation_id'],
                'production_order_operations_bom_unique'
            );

            $table->index(
                'production_operation_id',
                'production_order_operations_operation_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_operations');
    }
};