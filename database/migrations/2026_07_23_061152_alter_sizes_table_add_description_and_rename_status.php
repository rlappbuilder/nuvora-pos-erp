<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sizes', function (Blueprint $table) {
            $table->renameColumn('status', 'is_active');
            $table->text('description')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('sizes', function (Blueprint $table) {
            $table->renameColumn('is_active', 'status');
            $table->dropColumn('description');
        });
    }
};