<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_issue_details', function (Blueprint $table) {
            $table->decimal('qty', 18, 6)->change();
            $table->decimal('unit_cost', 18, 6)->default(0)->change();
            $table->decimal('total_cost', 18, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_issue_details', function (Blueprint $table) {
            $table->decimal('qty', 18, 4)->change();
            $table->decimal('unit_cost', 18, 4)->default(0)->change();
            $table->decimal('total_cost', 18, 4)->default(0)->change();
        });
    }
};