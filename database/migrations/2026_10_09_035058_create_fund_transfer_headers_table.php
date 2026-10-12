<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_transfer_headers', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Document
            |--------------------------------------------------------------------------
            */

            $table->string('transfer_number', 50)
                ->unique();

            $table->date('transfer_date');


            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            */

            $table->foreignId('from_branch_id')
                ->nullable()
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('from_account_id')
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Destination
            |--------------------------------------------------------------------------
            */

            $table->foreignId('to_branch_id')
                ->nullable()
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('to_account_id')
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 15, 2);


            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'Draft',
                'Submitted',
                'Approved',
                'Rejected',
                'Posted',
                'Cancelled',
            ])->default('Draft')->index();


            /*
            |--------------------------------------------------------------------------
            | Submitted
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
            | Approved
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
            | Rejected
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
            | Posted
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
            | Cancelled
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

            $table->index([
                'company_id',
                'transfer_date',
            ]);

            $table->index([
                'company_id',
                'status',
            ]);

            $table->index([
                'from_branch_id',
            ]);

            $table->index([
                'to_branch_id',
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'fund_transfer_headers'
        );
    }
};