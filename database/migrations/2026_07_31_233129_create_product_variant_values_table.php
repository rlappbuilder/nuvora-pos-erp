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
        Schema::create('product_variant_values', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relation
            |--------------------------------------------------------------------------
            */

            $table->foreignId('product_variant_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_attribute_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('product_attribute_value_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'product_variant_id',
                    'product_attribute_id',
                ],
                'pvv_variant_attr_unique'
            );

            $table->index(
                'product_attribute_value_id',
                'pvv_attr_value_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_values');
    }
};