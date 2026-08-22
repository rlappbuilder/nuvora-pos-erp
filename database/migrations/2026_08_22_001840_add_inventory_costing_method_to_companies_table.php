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
    Schema::table('companies', function (Blueprint $table) {

        $table
            ->enum(
                'inventory_costing_method',
                [
                    'WEIGHTED_AVERAGE',
                    'FIFO',
                    'LIFO',
                ]
            )
            ->default('WEIGHTED_AVERAGE')
            ->after('status');

    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('companies', function (Blueprint $table) {

        $table->dropColumn(
            'inventory_costing_method'
        );

    });
}
};
