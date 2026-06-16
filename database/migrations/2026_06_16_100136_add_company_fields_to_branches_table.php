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
    Schema::table('branches', function (Blueprint $table) {

        $table->foreignId('company_id')
            ->nullable()
            ->after('id')
            ->constrained()
            ->nullOnDelete();

        $table->string('manager_name')
            ->nullable()
            ->after('email');

        $table->string('city')
            ->nullable()
            ->after('address');

        $table->string('province')
            ->nullable()
            ->after('city');

        $table->boolean('is_head_office')
            ->default(false)
            ->after('province');

        $table->softDeletes();

    });
}

public function down(): void
{
    Schema::table('branches', function (Blueprint $table) {

        $table->dropForeign([
            'company_id'
        ]);

        $table->dropColumn([

            'company_id',

            'manager_name',

            'city',

            'province',

            'is_head_office',

            'deleted_at'

        ]);

    });
}

};
