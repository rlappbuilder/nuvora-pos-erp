<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resellers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('reseller_code', 50);

            $table->string('name');

            $table->string('contact_person')
                ->nullable();

            $table->string('phone', 50)
                ->nullable();

            $table->string('email')
                ->nullable();

            $table->text('address')
                ->nullable();

            $table->string('city', 100)
                ->nullable();

            $table->string('tax_number', 100)
                ->nullable();

            $table->boolean('status')
                ->default(true);

            $table->unsignedBigInteger('created_by')
                ->nullable();

            $table->unsignedBigInteger('updated_by')
                ->nullable();

            $table->softDeletes();

            $table->timestamps();

            $table->unique([
                'company_id',
                'reseller_code',
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resellers');
    }
};