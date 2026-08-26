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
            Schema::rename(
                'goods_receipts_headers',
                'goods_receipt_headers'
            );
        }

        public function down(): void
        {
            Schema::rename(
                'goods_receipt_headers',
                'goods_receipts_header'
            );
        }

};
