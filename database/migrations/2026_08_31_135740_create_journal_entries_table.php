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
        Schema::create('journal_entries', function (Blueprint $table) {

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Organization
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Accounting Structure
            |--------------------------------------------------------------------------
            */

            $table->foreignId('accounting_journal_id')
                ->constrained('accounting_journals')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('fiscal_year_id')
                ->constrained('fiscal_years')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('accounting_period_id')
                ->constrained('accounting_periods')
                ->cascadeOnUpdate()
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Journal Entry
            |--------------------------------------------------------------------------
            */

            $table->string('code', 50);

            $table->date('entry_date');

            $table->string('reference', 100)
                ->nullable();

            $table->text('description')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'Draft',
                'Posted',
                'Reversed',
            ])->default('Draft');


            /*
            |--------------------------------------------------------------------------
            | Posting
            |--------------------------------------------------------------------------
            */

            $table->foreignId('posted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')
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


            /*
            |--------------------------------------------------------------------------
            | Timestamps / Soft Delete
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();


            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'company_id',
                    'code',
                ],
                'je_company_code_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                'entry_date',
                'je_entry_date_idx'
            );

            $table->index(
                'status',
                'je_status_idx'
            );

            $table->index(
                [
                    'company_id',
                    'branch_id',
                ],
                'je_company_branch_idx'
            );

            $table->index(
                [
                    'company_id',
                    'accounting_journal_id',
                ],
                'je_company_journal_idx'
            );

            $table->index(
                [
                    'company_id',
                    'fiscal_year_id',
                    'accounting_period_id',
                ],
                'je_company_period_idx'
            );

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};