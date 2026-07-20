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
    Schema::create('account_types', function (Blueprint $table) {

        $table->id();

        $table->foreignId('account_group_id')
            ->constrained('account_groups')
            ->cascadeOnUpdate()
            ->restrictOnDelete();

        $table->string('code', 10);

        $table->string('name', 150);

        $table->text('description')
            ->nullable();

        $table->unsignedSmallInteger('sort_order')
            ->default(0);

        $table->boolean('status')
            ->default(true);

        $table->foreignId('created_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->foreignId('updated_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->foreignId('deleted_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->timestamps();

        $table->softDeletes();

        $table->unique('code');

        $table->index('account_group_id');

        $table->index('status');

        $table->index('sort_order');

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
