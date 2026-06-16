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
    Schema::table('warehouses', function (Blueprint $table) {

        $table->string('pic_name')
            ->nullable()
            ->after('name');

        $table->string('phone')
            ->nullable()
            ->after('pic_name');

        $table->string('email')
            ->nullable()
            ->after('phone');

    });
}
};
