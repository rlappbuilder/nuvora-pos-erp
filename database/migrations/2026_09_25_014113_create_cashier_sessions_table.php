<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_sessions', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Company & Branch Context
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Cashier
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Accounting Cash Account
            |--------------------------------------------------------------------------
            |
            | Account used as the cashier's cash account for POS
            | accounting transactions.
            |
            */

            $table->foreignId('cash_account_id')
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Previous Cashier Session
            |--------------------------------------------------------------------------
            |
            | Used for carry-forward cash from the previous session.
            |
            */

            $table->foreignId('previous_session_id')
                ->nullable()
                ->constrained('cashier_sessions')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Session Information
            |--------------------------------------------------------------------------
            */

            $table->string(
                'session_number',
                50
            );

            $table->dateTime(
                'opened_at'
            );

            /*
            |--------------------------------------------------------------------------
            | Opening Cash
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'opening_balance',
                15,
                2
            )->default(0);

            /*
            |--------------------------------------------------------------------------
            | Closing Cash
            |--------------------------------------------------------------------------
            */

            $table->dateTime(
                'closed_at'
            )->nullable();

            $table->decimal(
                'closing_balance',
                15,
                2
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Session Status
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'status',
                [
                    'open',
                    'closed',
                ]
            )->default('open');

            /*
            |--------------------------------------------------------------------------
            | Closing Note
            |--------------------------------------------------------------------------
            */

            $table->text(
                'closing_note'
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Unique
            |--------------------------------------------------------------------------
            */

            $table->unique(
                'session_number'
            );

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
                'user_id',
                'branch_id',
                'status',
            ]);

            $table->index([
                'branch_id',
                'status',
            ]);

            $table->index(
                'cash_account_id'
            );

            $table->index(
                'previous_session_id'
            );

            $table->index(
                'opened_at'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'cashier_sessions'
        );
    }
};