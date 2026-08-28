<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'purchase_return_headers',
            function (Blueprint $table) {

                /*
                |--------------------------------------------------------------------------
                | Company / Branch
                |--------------------------------------------------------------------------
                */

                $table->foreignId('company_id')
                    ->after('id')
                    ->constrained('companies')
                    ->restrictOnDelete();

                $table->foreignId('branch_id')
                    ->after('company_id')
                    ->constrained('branches')
                    ->restrictOnDelete();

            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'purchase_return_headers',
            function (Blueprint $table) {

                $table->dropForeign([
                    'company_id',
                ]);

                $table->dropForeign([
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