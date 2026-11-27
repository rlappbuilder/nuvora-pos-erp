<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opening_stock_details', function (Blueprint $table) {
            $table->decimal('qty', 18, 6)->change();
            $table->decimal('unit_cost', 18, 6)->change();
        });
    }

    public function down(): void
    {
        Schema::table('opening_stock_details', function (Blueprint $table) {
            $table->decimal('qty', 18, 2)->change();
            $table->decimal('unit_cost', 18, 2)->change();
        });
    }
};