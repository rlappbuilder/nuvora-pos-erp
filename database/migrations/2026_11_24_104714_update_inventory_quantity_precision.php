<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->decimal('on_hand_qty', 18, 6)
                ->default(0)
                ->change();

            $table->decimal('reserved_qty', 18, 6)
                ->default(0)
                ->change();

            $table->decimal('available_qty', 18, 6)
                ->default(0)
                ->change();

            $table->decimal('average_cost', 18, 6)
                ->default(0)
                ->change();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('qty_in', 18, 6)
                ->default(0)
                ->change();

            $table->decimal('qty_out', 18, 6)
                ->default(0)
                ->change();

            $table->decimal('balance_qty', 18, 6)
                ->default(0)
                ->change();

            $table->decimal('unit_cost', 18, 6)
                ->default(0)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->decimal('on_hand_qty', 18, 2)
                ->default(0)
                ->change();

            $table->decimal('reserved_qty', 18, 2)
                ->default(0)
                ->change();

            $table->decimal('available_qty', 18, 2)
                ->default(0)
                ->change();

            $table->decimal('average_cost', 18, 2)
                ->default(0)
                ->change();
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->decimal('qty_in', 18, 2)
                ->default(0)
                ->change();

            $table->decimal('qty_out', 18, 2)
                ->default(0)
                ->change();

            $table->decimal('balance_qty', 18, 2)
                ->default(0)
                ->change();

            $table->decimal('unit_cost', 18, 2)
                ->default(0)
                ->change();
        });
    }
};