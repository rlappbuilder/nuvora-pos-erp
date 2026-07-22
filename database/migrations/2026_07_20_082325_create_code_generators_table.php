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
    Schema::create('code_generators', function (Blueprint $table) {

        $table->id();

        $table->foreignId('company_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();

        $table->string('module', 100);

        $table->string('prefix', 20);

        $table->string('format', 100);

        $table->string('separator', 10)
            ->nullable();

        $table->unsignedInteger('digit')
            ->default(4);

        $table->unsignedBigInteger('next_number')
            ->default(1);

        $table->enum('reset_type', [
            'Never',
            'Daily',
            'Monthly',
            'Yearly',
        ])->default('Never');

        $table->timestamp('last_reset_at')
            ->nullable();

        $table->boolean('is_active')
            ->default(true);

        $table->timestamps();

        $table->unique([
            'company_id',
            'module',
        ]);

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_generators');
    }
};
