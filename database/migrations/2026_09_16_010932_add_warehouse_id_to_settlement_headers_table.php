<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_headers', function (Blueprint $table) {
            $table->foreignId('warehouse_id')
                ->after('branch_id')
                ->constrained()
                ->cascadeOnUpdate();

            $table->dropIndex('settlement_context_idx');

            $table->index(
                [
                    'company_id',
                    'branch_id',
                    'warehouse_id',
                    'reseller_id',
                    'settlement_date',
                ],
                'settlement_context_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('settlement_headers', function (Blueprint $table) {
            $table->dropIndex('settlement_context_idx');
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');

            $table->index(
                [
                    'company_id',
                    'branch_id',
                    'reseller_id',
                    'settlement_date',
                ],
                'settlement_context_idx'
            );
        });
    }
};