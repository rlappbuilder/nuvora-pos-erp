<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'purchase_invoice_headers',
            function (Blueprint $table) {

                $table->string('number')
                    ->unique()
                    ->after('id');

            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'purchase_invoice_headers',
            function (Blueprint $table) {

                $table->dropUnique([
                    'number',
                ]);

                $table->dropColumn(
                    'number'
                );

            }
        );
    }
};