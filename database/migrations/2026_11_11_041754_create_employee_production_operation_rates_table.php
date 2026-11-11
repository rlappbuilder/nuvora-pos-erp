<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_production_operation_rates', function (Blueprint $table) {
            $table->id();

            // Employee Production Operation
            $table->foreignId('employee_production_operation_id');

            $table->foreign(
                'employee_production_operation_id',
                'epor_operation_fk'
            )
                ->references('id')
                ->on('employee_production_operations')
                ->cascadeOnDelete();

            // Product
            $table->foreignId('product_id');

            $table->foreign('product_id', 'epor_product_fk')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            // Wage Rate
            $table->decimal('wage_rate', 18, 2)->default(0);

            // Status
            $table->boolean('is_active')->default(true);

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            // Timestamps
            $table->softDeletes();
            $table->timestamps();

            // Unique Combination
            $table->unique(
                [
                    'employee_production_operation_id',
                    'product_id',
                ],
                'epor_operation_product_unique'
            );

            // Index
            $table->index(
                ['product_id', 'is_active'],
                'epor_product_active_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_production_operation_rates');
    }
};