<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_production_operations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->foreignId('production_operation_id')
                ->constrained('production_operations')
                ->restrictOnDelete();

            $table->decimal('wage_rate', 18, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->unique(
                [
                    'company_id',
                    'employee_id',
                    'production_operation_id',
                ],
                'employee_production_operations_unique'
            );

            $table->index(
                ['company_id', 'is_active'],
                'employee_production_operations_company_active_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_production_operations');
    }
};