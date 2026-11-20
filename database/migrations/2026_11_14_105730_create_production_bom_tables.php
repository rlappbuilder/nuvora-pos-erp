<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Production BOMs
        |--------------------------------------------------------------------------
        */

        Schema::create('production_boms', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | BOM Identity
            |--------------------------------------------------------------------------
            */

            $table->string('code', 50);

            $table->string('name', 150);

            $table->enum('bom_type', [
                'standard',
                'alternative',
            ])->default('standard');

            $table->text('description')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['company_id', 'branch_id', 'code'],
                'production_boms_branch_code_unique'
            );

            $table->index(
                ['company_id', 'branch_id', 'product_variant_id'],
                'production_boms_product_index'
            );

            $table->index(
                ['company_id', 'branch_id', 'bom_type', 'is_active'],
                'production_boms_type_active_index'
            );
        });


        /*
        |--------------------------------------------------------------------------
        | 2. Production BOM Versions
        |--------------------------------------------------------------------------
        */

        Schema::create('production_bom_versions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('production_bom_id')
                ->constrained('production_boms')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Version
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('version');

            /*
            |--------------------------------------------------------------------------
            | Output
            |--------------------------------------------------------------------------
            */

            $table->decimal('output_qty', 18, 4)
                ->default(1);

            $table->foreignId('output_unit_id')
                ->constrained('units')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Effective Period
            |--------------------------------------------------------------------------
            */

            $table->date('effective_from')
                ->nullable();

            $table->date('effective_until')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'active',
                'inactive',
            ])->default('draft');

            $table->text('revision_notes')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['production_bom_id', 'version'],
                'production_bom_versions_unique'
            );

            $table->index(
                ['production_bom_id', 'status'],
                'production_bom_versions_status_index'
            );

            $table->index(
                ['effective_from', 'effective_until'],
                'production_bom_versions_effective_index'
            );
        });


        /*
        |--------------------------------------------------------------------------
        | 3. Production BOM Operations
        |--------------------------------------------------------------------------
        */

        Schema::create('production_bom_operations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('production_bom_version_id')
                ->constrained('production_bom_versions')
                ->cascadeOnDelete();

            $table->foreignId('production_operation_id')
                ->constrained('production_operations')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Routing
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
                ['production_bom_version_id', 'sequence'],
                'production_bom_operations_sequence_unique'
            );

            $table->index(
                'production_operation_id',
                'production_bom_operations_operation_index'
            );
        });


        /*
        |--------------------------------------------------------------------------
        | 4. Production BOM Materials
        |--------------------------------------------------------------------------
        */

        Schema::create('production_bom_materials', function (Blueprint $table) {

            $table->id();

            $table->foreignId('production_bom_version_id')
                ->constrained('production_bom_versions')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Component
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
            | Quantity and Waste
            |--------------------------------------------------------------------------
            */

            $table->decimal('qty', 18, 4);

            $table->decimal('scrap_percent', 8, 4)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Production Operation
            |--------------------------------------------------------------------------
            */

            $table->foreignId('production_bom_operation_id')
                ->nullable()
                ->constrained('production_bom_operations')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Sequence and Notes
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

            $table->index(
                ['production_bom_version_id', 'sequence'],
                'production_bom_materials_sequence_index'
            );

            $table->index(
                'component_variant_id',
                'production_bom_materials_component_index'
            );

            $table->index(
                'production_bom_operation_id',
                'production_bom_materials_operation_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_bom_materials');

        Schema::dropIfExists('production_bom_operations');

        Schema::dropIfExists('production_bom_versions');

        Schema::dropIfExists('production_boms');
    }
};