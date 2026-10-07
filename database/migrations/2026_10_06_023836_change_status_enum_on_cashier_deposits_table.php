<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Normalize Existing Status
        |--------------------------------------------------------------------------
        */

        DB::table('cashier_deposits')
            ->where('status', 'draft')
            ->update([
                'status' => 'Draft',
            ]);

        DB::table('cashier_deposits')
            ->where('status', 'posted')
            ->update([
                'status' => 'Posted',
            ]);

        DB::table('cashier_deposits')
            ->where('status', 'cancelled')
            ->update([
                'status' => 'Cancelled',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Change Status Enum
        |--------------------------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE cashier_deposits
            MODIFY COLUMN status ENUM(
                'Draft',
                'Submitted',
                'Approved',
                'Rejected',
                'Received',
                'Posted',
                'Cancelled'
            ) NOT NULL DEFAULT 'Draft'
        ");
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Normalize Status Back
        |--------------------------------------------------------------------------
        */

        DB::table('cashier_deposits')
            ->where('status', 'Draft')
            ->update([
                'status' => 'draft',
            ]);

        DB::table('cashier_deposits')
            ->whereIn('status', [
                'Submitted',
                'Approved',
                'Rejected',
                'Received',
            ])
            ->update([
                'status' => 'draft',
            ]);

        DB::table('cashier_deposits')
            ->where('status', 'Posted')
            ->update([
                'status' => 'posted',
            ]);

        DB::table('cashier_deposits')
            ->where('status', 'Cancelled')
            ->update([
                'status' => 'cancelled',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Restore Original Enum
        |--------------------------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE cashier_deposits
            MODIFY COLUMN status ENUM(
                'draft',
                'posted',
                'cancelled'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};