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
       Schema::create('customers', function (Blueprint $table) {

    $table->id();

    $table->string('customer_code')
        ->unique();

    $table->string('name');

    $table->string('contact_person')
        ->nullable();

    $table->string('phone')
        ->nullable();

    $table->string('email')
        ->nullable();

    $table->text('address')
        ->nullable();

    $table->string('city')
        ->nullable();

    $table->string('tax_number')
        ->nullable();

    $table->integer('payment_term')
        ->default(0);

    $table->decimal(
        'credit_limit',
        18,
        2
    )->default(0);

    $table->boolean('status')
        ->default(true);

    $table->unsignedBigInteger('created_by')
        ->nullable();

    $table->unsignedBigInteger('updated_by')
        ->nullable();

    $table->timestamps();

    $table->softDeletes();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
