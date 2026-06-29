<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {

            $table->foreign('cash_bank_id')
                ->references('id')
                ->on('cash_banks')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {

            $table->dropForeign(['cash_bank_id']);

        });
    }
};