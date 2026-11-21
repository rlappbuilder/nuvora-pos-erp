<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_results', function (Blueprint $table) {

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
            | Production Order
            |--------------------------------------------------------------------------
            */

            $table->foreignId('production_order_header_id')
                ->constrained('production_order_headers')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Document Identity
            |--------------------------------------------------------------------------
            */

            $table->string('number', 50);

            $table->date('result_date');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->string('status', 30)
                ->default('Draft');

            /*
            |--------------------------------------------------------------------------
            | Posting
            |--------------------------------------------------------------------------
            */

            $table->timestamp('posted_at')
                ->nullable();

            $table->foreignId('posted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

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

            $table->text('cancel_reason')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Information
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
                'production_results_number_unique'
            );

            $table->index(
                ['production_order_header_id', 'status'],
                'production_results_order_status_index'
            );

            $table->index(
                ['company_id', 'branch_id', 'result_date'],
                'production_results_date_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_results');
    }
};