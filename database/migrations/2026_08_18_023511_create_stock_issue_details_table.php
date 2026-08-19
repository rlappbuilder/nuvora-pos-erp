<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_issue_details', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary Key
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Header
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'stock_issue_header_id'
            )
                ->constrained(
                    'stock_issue_headers'
                )
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'product_variant_id'
            )
                ->constrained(
                    'product_variants'
                )
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Unit
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'unit_id'
            )
                ->constrained(
                    'units'
                )
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Quantity
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'qty',
                18,
                4
            );


            /*
            |--------------------------------------------------------------------------
            | Cost
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'unit_cost',
                18,
                4
            )
                ->default(0);

            $table->decimal(
                'total_cost',
                18,
                4
            )
                ->default(0);


            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            $table->text(
                'description'
            )
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId(
                'created_by'
            )
                ->nullable()
                ->constrained(
                    'users'
                )
                ->nullOnDelete();

            $table->foreignId(
                'updated_by'
            )
                ->nullable()
                ->constrained(
                    'users'
                )
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();


            /*
            |--------------------------------------------------------------------------
            | Index
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'stock_issue_header_id',
                    'product_variant_id',
                ],
                'sid_header_variant_idx'
            );

            $table->index(
                [
                    'product_variant_id',
                    'unit_id',
                ],
                'sid_variant_unit_idx'
            );
        });
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'stock_issue_details'
        );
    }
};