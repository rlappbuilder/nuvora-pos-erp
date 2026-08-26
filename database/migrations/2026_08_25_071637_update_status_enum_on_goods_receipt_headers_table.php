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

                $table->enum('status', [
                    'Draft',
                    'Submitted',
                    'Rejected',
                    'Approved',
                    'Posted',
                    'Cancelled',
                ])
                    ->default('Draft')
                    ->change();

            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'goods_receipts_headers',
            function (Blueprint $table) {

                $table->enum('status', [
                    'Draft',
                    'Posted',
                    'Cancelled',
                ])
                    ->default('Draft')
                    ->change();

            }
        );
    }
};