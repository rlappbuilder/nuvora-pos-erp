<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reseller_price_histories', function (Blueprint $table) {
            $table->dateTime('effective_from')
                ->change();

            $table->dateTime('effective_to')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('reseller_price_histories', function (Blueprint $table) {
            $table->date('effective_from')
                ->change();

            $table->date('effective_to')
                ->nullable()
                ->change();
        });
    }
};