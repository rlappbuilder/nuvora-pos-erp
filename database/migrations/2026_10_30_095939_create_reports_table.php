<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Report Identity
            |--------------------------------------------------------------------------
            */

            $table->string('code', 100)->unique();

            $table->string('name');

            $table->string('category', 50);

            $table->text('description')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Report Configuration
            |--------------------------------------------------------------------------
            */

            $table->string('route_name')->nullable();

            $table->json('available_formats')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Status & Ordering
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);


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

            $table->index('category');

            $table->index('is_active');

            $table->index([
                'category',
                'is_active',
                'sort_order',
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};