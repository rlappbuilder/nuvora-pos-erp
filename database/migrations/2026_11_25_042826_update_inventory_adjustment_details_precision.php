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
            Schema::table('inventory_adjustment_details', function (Blueprint $table) {
                $table->decimal('system_qty', 18, 6)->change();
                $table->decimal('actual_qty', 18, 6)->change();
                $table->decimal('difference_qty', 18, 6)->change();
                $table->decimal('unit_cost', 18, 6)->change();
            });
        }

        public function down(): void
        {
            Schema::table('inventory_adjustment_details', function (Blueprint $table) {
                $table->decimal('system_qty', 18, 2)->change();
                $table->decimal('actual_qty', 18, 2)->change();
                $table->decimal('difference_qty', 18, 2)->change();
                $table->decimal('unit_cost', 18, 2)->change();
            });
        }
};
