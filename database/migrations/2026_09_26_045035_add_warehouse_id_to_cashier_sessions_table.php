<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. warehouse_id sudah terlanjur dibuat pada percobaan migration
        |    pertama. Ubah menjadi nullable terlebih dahulu.
        |--------------------------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE cashier_sessions
            MODIFY warehouse_id BIGINT UNSIGNED NULL
        ");

        /*
        |--------------------------------------------------------------------------
        | 2. Bersihkan warehouse_id yang tidak valid.
        |--------------------------------------------------------------------------
        */

        DB::table('cashier_sessions')
            ->whereNotNull('warehouse_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('warehouses')
                    ->whereColumn(
                        'warehouses.id',
                        'cashier_sessions.warehouse_id'
                    );
            })
            ->update([
                'warehouse_id' => null,
            ]);

        /*
        |--------------------------------------------------------------------------
        | 3. Tambahkan foreign key.
        |--------------------------------------------------------------------------
        */

        Schema::table('cashier_sessions', function (Blueprint $table) {
            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->restrictOnDelete();

            $table->index('warehouse_id');
        });
    }

    public function down(): void
    {
        Schema::table('cashier_sessions', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropIndex(['warehouse_id']);
        });

        DB::statement("
            ALTER TABLE cashier_sessions
            DROP COLUMN warehouse_id
        ");
    }
};