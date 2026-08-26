<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'goods_receipt_headers',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Organization
                |--------------------------------------------------------------------------
                */

                $table->foreignId('company_id')
                    ->nullable()
                    ->after('grn_number')
                    ->constrained('companies')
                    ->restrictOnDelete();

                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('company_id')
                    ->constrained('branches')
                    ->restrictOnDelete();

                /*
                |--------------------------------------------------------------------------
                | Index
                |--------------------------------------------------------------------------
                */

                $table->index([
                    'company_id',
                    'branch_id',
                ]);

            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'goods_receipts_headers',
            function (Blueprint $table) {

                $table->dropForeign([
                    'branch_id',
                ]);

                $table->dropForeign([
                    'company_id',
                ]);

                $table->dropIndex([
                    'company_id',
                    'branch_id',
                ]);

                $table->dropColumn([
                    'company_id',
                    'branch_id',
                ]);

            }
        );
    }
};