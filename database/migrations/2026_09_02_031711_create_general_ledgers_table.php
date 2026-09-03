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
        Schema::create('general_ledgers', function (Blueprint $table) {

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
            | Account
            |--------------------------------------------------------------------------
            */

            $table->foreignId('account_id')
                ->constrained('chart_of_accounts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Source Journal Entry
            |--------------------------------------------------------------------------
            */

            $table->foreignId('journal_entry_id')
                ->constrained('journal_entries')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('journal_entry_line_id')
                ->constrained('journal_entry_lines')
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
            | Transaction
            |--------------------------------------------------------------------------
            */

            $table->date('entry_date');

            $table->string('reference', 100)
                ->nullable();

            $table->text('description')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal('debit', 18, 2)
                ->default(0);

            $table->decimal('credit', 18, 2)
                ->default(0);

            $table->decimal('running_balance', 18, 2)
                ->default(0);


            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */

            $table->unique(
                'journal_entry_line_id',
                'gl_journal_entry_line_unique'
            );


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'company_id',
                    'account_id',
                    'entry_date',
                ],
                'gl_company_account_date_idx'
            );

            $table->index(
                [
                    'company_id',
                    'fiscal_year_id',
                    'accounting_period_id',
                ],
                'gl_company_period_idx'
            );

            $table->index(
                [
                    'account_id',
                    'entry_date',
                ],
                'gl_account_date_idx'
            );

            $table->index(
                'journal_entry_id',
                'gl_journal_entry_idx'
            );

            $table->index(
                'branch_id',
                'gl_branch_idx'
            );

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledgers');
    }
};