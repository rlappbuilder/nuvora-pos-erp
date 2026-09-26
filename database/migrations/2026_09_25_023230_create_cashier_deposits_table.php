<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_deposits', function (Blueprint $table) {
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
            | Cashier Session
            |--------------------------------------------------------------------------
            */

            $table->foreignId('cashier_session_id')
                ->constrained('cashier_sessions')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Deposit Number
            |--------------------------------------------------------------------------
            */

            $table->string(
                'deposit_number',
                50
            );

            /*
            |--------------------------------------------------------------------------
            | Destination Accounting Account
            |--------------------------------------------------------------------------
            |
            | Account receiving the deposited cash.
            |
            | Examples:
            | - Main Cash
            | - Bank BCA
            | - Bank Mandiri
            |
            */

            $table->foreignId('destination_account_id')
                ->constrained('chart_of_accounts')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Deposit Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'amount',
                15,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Deposit Date
            |--------------------------------------------------------------------------
            */

            $table->dateTime(
                'deposited_at'
            );

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum(
                'status',
                [
                    'draft',
                    'posted',
                    'cancelled',
                ]
            )->default('draft');

            /*
            |--------------------------------------------------------------------------
            | Note
            |--------------------------------------------------------------------------
            */

            $table->text(
                'note'
            )->nullable();

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
                'deposit_number'
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

            $table->index(
                'cashier_session_id'
            );

            $table->index([
                'cashier_session_id',
                'status',
            ]);

            $table->index(
                'destination_account_id'
            );

            $table->index(
                'deposited_at'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'cashier_deposits'
        );
    }
};