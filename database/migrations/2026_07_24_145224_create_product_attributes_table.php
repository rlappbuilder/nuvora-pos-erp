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
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();

            // Company
            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Basic Information
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->string('display_name', 100);

            // Configuration
            $table->enum('input_type', [
                'Select',
                'Radio',
                'Button',
                'Color',
                'Text',
            ])->default('Select');

            $table->boolean('is_required')->default(false);
            $table->boolean('is_variant')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            // Description
            $table->text('description')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Audit
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

            $table->index('company_id');
            $table->index('code');
            $table->index('name');
            $table->index('is_variant');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};