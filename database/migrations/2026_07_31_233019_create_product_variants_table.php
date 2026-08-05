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
        Schema::create('product_variants', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relation
            |--------------------------------------------------------------------------
            */

           
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Variant
            |--------------------------------------------------------------------------
            */

            $table->string('sku',100);

            $table->string('barcode',100)
                ->nullable();

            $table->string('name');

            $table->string('slug')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Setting
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_default')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->unsignedInteger('sort_order')
                ->default(0);

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
            | Index
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'company_id',
                'sku',
            ]);

            $table->index([
                'company_id',
                'product_id',
            ]);

            $table->index([
                'product_id',
                'is_active',
            ]);

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};