<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_runs', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Report
            |--------------------------------------------------------------------------
            */

            $table->foreignId('report_id')
                ->constrained('reports')
                ->cascadeOnUpdate()
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Company & Branch
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Report Parameters
            |--------------------------------------------------------------------------
            */

            $table->json('filters')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Execution
            |--------------------------------------------------------------------------
            */

            $table->string('format', 20);

            $table->string('status', 20)->default('processing');

            $table->string('file_path')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Execution Time
            |--------------------------------------------------------------------------
            */

            $table->timestamp('started_at')->nullable();

            $table->timestamp('completed_at')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Error Information
            |--------------------------------------------------------------------------
            */

            $table->text('error_message')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('report_id');

            $table->index('company_id');

            $table->index('branch_id');

            $table->index('user_id');

            $table->index('status');

            $table->index('started_at');

            $table->index([
                'company_id',
                'branch_id',
                'started_at',
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_runs');
    }
};