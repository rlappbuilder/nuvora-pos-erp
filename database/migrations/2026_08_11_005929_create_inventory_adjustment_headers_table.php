<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'inventory_adjustment_headers',
            function (Blueprint $table) {

                $table->id();

                /*
                |--------------------------------------------------------------------------
                | Company / Location
                |--------------------------------------------------------------------------
                */

                $table->foreignId('company_id')
                    ->constrained('companies')
                    ->restrictOnDelete();

                $table->foreignId('branch_id')
                    ->constrained('branches')
                    ->restrictOnDelete();

                $table->foreignId('warehouse_id')
                    ->constrained('warehouses')
                    ->restrictOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Document
                |--------------------------------------------------------------------------
                */

                $table->string(
                    'number',
                    50
                )->unique();

                $table->date(
                    'transaction_date'
                );

                $table->enum(
                    'status',
                    [
                        'Draft',
                        'Rejected',
                        'Posted',
                    ]
                )->default('Draft');


                /*
                |--------------------------------------------------------------------------
                | Information
                |--------------------------------------------------------------------------
                */

                $table->text(
                    'description'
                )->nullable();


                /*
                |--------------------------------------------------------------------------
                | Posting
                |--------------------------------------------------------------------------
                */

                $table->timestamp(
                    'posted_at'
                )->nullable();

                $table->foreignId(
                    'posted_by'
                )->nullable()
                    ->constrained('users')
                    ->nullOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Rejection
                |--------------------------------------------------------------------------
                */

                $table->timestamp(
                    'rejected_at'
                )->nullable();

                $table->foreignId(
                    'rejected_by'
                )->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->text(
                    'rejected_reason'
                )->nullable();


                /*
                |--------------------------------------------------------------------------
                | Audit
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'created_by'
                )->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId(
                    'updated_by'
                )->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId(
                    'deleted_by'
                )->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->text(
                    'deleted_reason'
                )->nullable();


                /*
                |--------------------------------------------------------------------------
                | Timestamps / Soft Delete
                |--------------------------------------------------------------------------
                */

                $table->timestamps();

                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'inventory_adjustment_headers'
        );
    }
};