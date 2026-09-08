<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_payment_headers', function (Blueprint $table) {

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
            | Document
            |--------------------------------------------------------------------------
            */

            $table->string('number', 50)
                ->unique();

            $table->date('payment_date');

            /*
            |--------------------------------------------------------------------------
            | Supplier
            |--------------------------------------------------------------------------
            */

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            $table->string('payment_method', 30);

            /*
            |--------------------------------------------------------------------------
            | Cash / Bank Account
            |--------------------------------------------------------------------------
            |
            | Menunjuk ke Chart of Account yang digunakan
            | sebagai sumber pembayaran.
            |
            */

            $table->foreignId('payment_account_id')
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'total_amount',
                20,
                2
            )->default(0);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'status',
                [
                    'Draft',
                    'Submitted',
                    'Approved',
                    'Posted',
                    'Cancelled',
                ]
            )->default('Draft');

            /*
            |--------------------------------------------------------------------------
            | Information
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Approval / Posting
            |--------------------------------------------------------------------------
            */

            $table->timestamp('submitted_at')
                ->nullable();

            $table->foreignId('submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')
                ->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

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

            $table->index([
                'company_id',
                'branch_id',
            ]);

            $table->index([
                'supplier_id',
                'status',
            ]);

            $table->index('payment_date');

            $table->index('payment_account_id');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'purchase_payment_headers'
        );
    }
};