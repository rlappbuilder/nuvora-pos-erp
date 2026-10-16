<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inter_branch_settlement_headers', function (Blueprint $table) {

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
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Document
            |--------------------------------------------------------------------------
            */

            $table->string('settlement_number', 50)
                ->unique();

            $table->date('settlement_date');

            /*
            |--------------------------------------------------------------------------
            | Branch
            |--------------------------------------------------------------------------
            */

            $table->foreignId('from_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('to_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Payment Account
            |--------------------------------------------------------------------------
            */

            $table->foreignId('from_account_id')
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();

            $table->foreignId('to_account_id')
                ->nullable()
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
            | Payment Workflow
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
            | Receipt Workflow
            |--------------------------------------------------------------------------
            */

            $table->enum('receipt_status', [
                'Pending',
                'Received',
                'Rejected',
            ])->default('Pending')->index();

            /*
            |--------------------------------------------------------------------------
            | Payment Journal
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_journal_entry_id')
                ->nullable()
                ->unique()
                ->constrained('journal_entries')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Receipt Journal
            |--------------------------------------------------------------------------
            */

            $table->foreignId('receipt_journal_entry_id')
                ->nullable()
                ->unique()
                ->constrained('journal_entries')
                ->restrictOnDelete();

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
            | Received
            |--------------------------------------------------------------------------
            */

            $table->timestamp('received_at')
                ->nullable();

            $table->foreignId('received_by')
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

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                ['company_id', 'settlement_date'],
                'ibs_hdr_company_date_idx'
            );

            $table->index(
                ['company_id', 'from_branch_id', 'status'],
                'ibs_hdr_from_status_idx'
            );

            $table->index(
                ['company_id', 'to_branch_id', 'receipt_status'],
                'ibs_hdr_to_receipt_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'inter_branch_settlement_headers'
        );
    }
};