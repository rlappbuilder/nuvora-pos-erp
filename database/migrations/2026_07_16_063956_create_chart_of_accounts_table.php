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
        Schema::create('chart_of_accounts', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Parent Account (Tree Structure)
            |--------------------------------------------------------------------------
            */
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Classification
            |--------------------------------------------------------------------------
            */
            $table->foreignId('account_category_id')
                ->constrained('account_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Account Information
            |--------------------------------------------------------------------------
            */
            $table->string('code', 20);

            $table->string('name', 150);

            $table->enum('normal_balance', [
                'Debit',
                'Credit',
            ]);

            $table->unsignedTinyInteger('level')
                ->default(1);

            $table->boolean('is_header')
                ->default(false);

            $table->boolean('is_posting')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Opening Balance
            |--------------------------------------------------------------------------
            */
            $table->decimal('opening_balance', 18, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            $table->boolean('status')
                ->default(true);

            $table->text('description')
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

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */
            $table->unique([
                'company_id',
                'code',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index('parent_id');
            $table->index('account_category_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};