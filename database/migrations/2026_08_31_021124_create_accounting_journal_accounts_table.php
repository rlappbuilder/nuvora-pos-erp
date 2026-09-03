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
        Schema::create(
            'accounting_journal_accounts',
            function (Blueprint $table) {

                $table->id();


                /*
                |--------------------------------------------------------------------------
                | Accounting Journal
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'accounting_journal_id'
                )
                    ->constrained(
                        'accounting_journals'
                    )
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Chart of Account
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'account_id'
                )
                    ->constrained(
                        'chart_of_accounts'
                    )
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();


                /*
                |--------------------------------------------------------------------------
                | Account Role
                |--------------------------------------------------------------------------
                */

                $table->enum(
                    'role',
                    [
                        'default',
                        'cash',
                        'bank',
                        'receivable',
                        'payable',
                        'revenue',
                        'expense',
                        'tax',
                    ]
                )
                    ->default('default');


                /*
                |--------------------------------------------------------------------------
                | Default Account
                |--------------------------------------------------------------------------
                */

                $table->boolean(
                    'is_default'
                )
                    ->default(false);


                /*
                |--------------------------------------------------------------------------
                | Audit
                |--------------------------------------------------------------------------
                */

                $table->foreignId(
                    'created_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId(
                    'updated_by'
                )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId(
                    'deleted_by'
                )
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
                        'accounting_journal_id',
                        'account_id',
                        'role',
                    ],
                    'journal_account_role_unique'
                );


                /*
                |--------------------------------------------------------------------------
                | Indexes
                |--------------------------------------------------------------------------
                */

                $table->index(
                    'accounting_journal_id',
                    'journal_account_journal_index'
                );

                $table->index(
                    'account_id',
                    'journal_account_coa_index'
                );

                $table->index(
                    'role',
                    'journal_account_role_index'
                );

                $table->index(
                    [
                        'accounting_journal_id',
                        'role',
                    ],
                    'journal_account_role_lookup_index'
                );

                $table->index(
                    [
                        'accounting_journal_id',
                        'is_default',
                    ],
                    'journal_account_default_index'
                );
            }
        );
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'accounting_journal_accounts'
        );
    }
};