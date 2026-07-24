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
    Schema::create('taxes', function (Blueprint $table) {
        $table->id();

        $table->string('code', 20)->unique();
        $table->string('name', 100);

        $table->enum('type', [
            'Percentage',
            'Fixed',
        ])->default('Percentage');

        $table->decimal('rate', 10, 2)->default(0);

        $table->boolean('is_default')->default(false);
        $table->boolean('is_active')->default(true);

        $table->text('description')->nullable();

        $table->foreignId('created_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->foreignId('updated_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->softDeletes();
        $table->timestamps();
    });
}
public function down(): void
{
    Schema::dropIfExists('taxes');
}
};
