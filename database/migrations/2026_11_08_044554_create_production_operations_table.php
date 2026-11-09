<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_operations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies');

            $table->string('code', 30);

            $table->string('name', 100);

            $table->text('description')
                ->nullable();

            $table->unsignedSmallInteger('sort_order')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->unsignedBigInteger('created_by')
                ->nullable();

            $table->unsignedBigInteger('updated_by')
                ->nullable();

            $table->unsignedBigInteger('deleted_by')
                ->nullable();

            $table->softDeletes();

            $table->timestamps();

            // Unique code per company
            $table->unique(
                ['company_id', 'code'],
                'production_operations_company_code_unique'
            );

            $table->index(
                ['company_id', 'is_active'],
                'production_operations_company_active_index'
            );

            $table->index('sort_order');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_operations');
    }
};