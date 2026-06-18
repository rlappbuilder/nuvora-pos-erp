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
    Schema::create('purchase_orders', function (Blueprint $table) {

        $table->id();

        $table->string('po_number')
            ->unique();

        $table->foreignId('supplier_id')
            ->constrained()
            ->cascadeOnUpdate();

        $table->foreignId('warehouse_id')
            ->constrained()
            ->cascadeOnUpdate();

        $table->date('order_date');

        $table->date('expected_date')
            ->nullable();

        $table->string('status')
            ->default('Draft');

        $table->text('remarks')
            ->nullable();

        $table->decimal(
            'subtotal',
            18,
            2
        )->default(0);

        $table->decimal(
            'tax_amount',
            18,
            2
        )->default(0);

        $table->decimal(
            'discount_amount',
            18,
            2
        )->default(0);

        $table->decimal(
            'grand_total',
            18,
            2
        )->default(0);

        $table->unsignedBigInteger(
            'created_by'
        )->nullable();

        $table->unsignedBigInteger(
            'updated_by'
        )->nullable();

        $table->timestamps();

        $table->softDeletes();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
