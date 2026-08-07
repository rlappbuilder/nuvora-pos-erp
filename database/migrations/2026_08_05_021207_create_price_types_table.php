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
        Schema::create('price_types', function (Blueprint $table) {

            $table->id();

            $table->string('code', 30)
                ->unique();

            $table->string('name', 100);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_default')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->text('description')
                ->nullable();

            $table->timestamps();

            $table->softDeletes();

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

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_types');
    }
};