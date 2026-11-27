<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE purchase_order_details
            MODIFY COLUMN unit_price DECIMAL(18,6)
            NOT NULL DEFAULT 0.000000
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE purchase_order_details
            MODIFY COLUMN unit_price DECIMAL(18,2)
            NOT NULL DEFAULT 0.00
        ");
    }
};