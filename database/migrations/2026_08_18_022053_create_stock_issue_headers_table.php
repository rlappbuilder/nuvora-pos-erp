<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_issue_headers', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

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

            $table->string('number', 50)
                ->unique();

            $table->date('transaction_date');

            $table->string('issue_type', 50);

            $table->enum('status', [
                'Draft',
                'Rejected',
                'Posted',
            ])->default('Draft');

            $table->text('description')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Rejection
            |--------------------------------------------------------------------------
            */

            $table->text('rejected_reason')
                ->nullable();

            $table->timestamp('rejected_at')
                ->nullable();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


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


            /*
            |--------------------------------------------------------------------------
            | Soft Delete
            |--------------------------------------------------------------------------
            */

            $table->softDeletes();


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

            $table->index([
                'company_id',
                'branch_id',
                'warehouse_id',
            ]);

            $table->index([
                'transaction_date',
                'status',
            ]);

            $table->index('issue_type');

        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'stock_issue_headers'
        );
    }
};