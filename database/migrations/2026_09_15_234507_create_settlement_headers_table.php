<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settlement_headers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Company & Transaction Context
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnUpdate();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnUpdate();

            $table->foreignId('reseller_id')
                ->constrained()
                ->cascadeOnUpdate();

            /*
            |--------------------------------------------------------------------------
            | Document Information
            |--------------------------------------------------------------------------
            */

            $table->string('settlement_number');

            $table->date('settlement_date');

            $table->date('period_from');

            $table->date('period_to');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'Posted',
                'Cancelled',
            ])->default('Posted');

            /*
            |--------------------------------------------------------------------------
            | Amounts
            |--------------------------------------------------------------------------
            */

            $table->decimal('subtotal', 18, 2)
                ->default(0);

            $table->decimal('adjustment_amount', 18, 2)
                ->default(0);

            $table->decimal('grand_total', 18, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Remarks
            |--------------------------------------------------------------------------
            */

            $table->text('remarks')
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

            $table->foreignId('posted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Cancellation
            |--------------------------------------------------------------------------
            */

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')
                ->nullable();

            $table->text('cancel_reason')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps & Soft Deletes
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'company_id',
                'settlement_number',
            ]);

           $table->index(
                [
                    'company_id',
                    'branch_id',
                    'reseller_id',
                    'settlement_date',
                ],
                'settlement_context_idx'
            );

           $table->index(
                [
                    'reseller_id',
                    'period_from',
                    'period_to',
                ],
                'settlement_period_idx'
            );

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlement_headers');
    }
};