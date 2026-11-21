<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_order_headers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Organization
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
            | Document Identity
            |--------------------------------------------------------------------------
            */

            $table->string('number', 50);

            /*
            |--------------------------------------------------------------------------
            | Production BOM
            |--------------------------------------------------------------------------
            */

            $table->foreignId('production_bom_id')
                ->constrained('production_boms')
                ->restrictOnDelete();

            $table->foreignId('production_bom_version_id')
                ->constrained('production_bom_versions')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Warehouse
            |--------------------------------------------------------------------------
            */

            $table->foreignId('source_warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->foreignId('destination_warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Production Dates
            |--------------------------------------------------------------------------
            */

            $table->date('order_date');

            $table->date('planned_start_date')
                ->nullable();

            $table->date('planned_end_date')
                ->nullable();

            $table->date('actual_start_date')
                ->nullable();

            $table->date('actual_end_date')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Workflow Status
            |--------------------------------------------------------------------------
            */

            $table->string('status', 30)
                ->default('Draft');

            /*
            |--------------------------------------------------------------------------
            | Submission
            |--------------------------------------------------------------------------
            */

            $table->timestamp('submitted_at')
                ->nullable();

            $table->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            $table->timestamp('approved_at')
                ->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Rejection
            |--------------------------------------------------------------------------
            */

            $table->timestamp('rejected_at')
                ->nullable();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('rejected_reason')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Cancellation
            |--------------------------------------------------------------------------
            */

            $table->timestamp('cancelled_at')
                ->nullable();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('cancelled_reason')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            $table->text('description')
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

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('deleted_reason')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                ['company_id', 'branch_id', 'number'],
                'production_order_headers_number_unique'
            );

            $table->index(
                ['company_id', 'branch_id', 'status'],
                'production_order_headers_status_index'
            );

            $table->index(
                ['production_bom_id', 'production_bom_version_id'],
                'production_order_headers_bom_index'
            );

            $table->index(
                ['source_warehouse_id', 'destination_warehouse_id'],
                'production_order_headers_warehouse_index'
            );

            $table->index(
                ['planned_start_date', 'planned_end_date'],
                'production_order_headers_schedule_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_headers');
    }
};