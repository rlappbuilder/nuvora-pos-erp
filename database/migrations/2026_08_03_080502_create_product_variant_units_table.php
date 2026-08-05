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
            Schema::create('product_variant_units', function (Blueprint $table) {

        $table->id();

        /*
        |--------------------------------------------------------------------------
        | Relations
        |--------------------------------------------------------------------------
        */

        $table->foreignId('product_variant_id')
            ->constrained()
            ->cascadeOnUpdate()
            ->cascadeOnDelete();

        $table->foreignId('unit_id')
            ->constrained()
            ->cascadeOnUpdate()
            ->restrictOnDelete();

        /*
        |--------------------------------------------------------------------------
        | Conversion
        |--------------------------------------------------------------------------
        */

        $table->decimal(
            'conversion_factor',
            18,
            6
        )->default(1);

        /*
        |--------------------------------------------------------------------------
        | Flags
        |--------------------------------------------------------------------------
        */

        $table->boolean('is_base')
            ->default(false);

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

        $table->unique(
            [
                'product_variant_id',
                'unit_id',
            ],
            'pvu_variant_unit_unique'
        );

        $table->index(
            [
                'product_variant_id',
                'is_active',
            ],
            'pvu_variant_active_idx'
        );

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_units');
    }
};
