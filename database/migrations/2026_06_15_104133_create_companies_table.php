<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {

            $table->id();

            $table->string('company_code', 50)->unique();

            $table->string('company_name');

            $table->string('legal_name')->nullable();

            $table->string('phone', 50)->nullable();

            $table->string('email')->nullable();

            $table->string('website')->nullable();

            $table->string('tax_number')->nullable();

            $table->string('director_name')->nullable();

            $table->string('logo')->nullable();

            $table->text('address')->nullable();

            $table->string('city')->nullable();

            $table->string('province')->nullable();

            $table->string('postal_code')->nullable();

            $table->boolean('status')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();

            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};