<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consignment_outs', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Company & Location
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('reseller_id')
                ->constrained('resellers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Document
            |--------------------------------------------------------------------------
            */

            $table->string('consignment_out_number', 50);

            $table->dateTime('transaction_date');

            $table->string('reference_number', 100)
                ->nullable();

            $table->dateTime('posting_date')
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
            ])->default('Draft');


            /*
            |--------------------------------------------------------------------------
            | Information
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Submission
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('submitted_by')
                ->nullable();

            $table->dateTime('submitted_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('approved_by')
                ->nullable();

            $table->dateTime('approved_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Rejection
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('rejected_by')
                ->nullable();

            $table->dateTime('rejected_at')
                ->nullable();

            $table->text('rejected_reason')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Posting
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('posted_by')
                ->nullable();

            $table->dateTime('posted_at')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Cancellation
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('cancelled_by')
                ->nullable();

            $table->dateTime('cancelled_at')
                ->nullable();

            $table->text('cancel_reason')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('created_by')
                ->nullable();

            $table->unsignedBigInteger('updated_by')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Timestamps & Soft Delete
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'company_id',
                    'consignment_out_number',
                ],
                'co_unique_number'
            );

            $table->index(
                [
                    'company_id',
                    'branch_id',
                    'transaction_date',
                ],
                'co_company_branch_date_idx'
            );

            $table->index(
                [
                    'reseller_id',
                    'status',
                ],
                'co_reseller_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consignment_outs');
    }
};