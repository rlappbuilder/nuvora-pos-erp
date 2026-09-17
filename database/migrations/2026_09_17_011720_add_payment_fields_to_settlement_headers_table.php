<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_headers', function (Blueprint $table) {
            $table->decimal('payment_amount', 18, 2)
                ->default(0)
                ->after('grand_total');

            $table->decimal('receivable_amount', 18, 2)
                ->default(0)
                ->after('payment_amount');

            $table->enum('payment_status', [
                'Paid',
                'Receivable',
            ])
                ->default('Receivable')
                ->after('receivable_amount');
        });
    }

    public function down(): void
    {
        Schema::table('settlement_headers', function (Blueprint $table) {
            $table->dropColumn([
                'payment_amount',
                'receivable_amount',
                'payment_status',
            ]);
        });
    }
};