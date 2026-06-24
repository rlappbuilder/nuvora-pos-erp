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
       Schema::create(

    'stock_transfers',

    function (

        Blueprint $table

    ) {

        $table->id();

        $table->string(
            'transfer_number'
        )->unique();

        $table->foreignId(
            'from_warehouse_id'
        );

        $table->foreignId(
            'to_warehouse_id'
        );

        $table->date(
            'transfer_date'
        );

        $table->string(
            'status'
        )->default(
            'Draft'
        );

        $table->text(
            'remarks'
        )->nullable();

        $table->foreignId(
            'created_by'
        )->nullable();

        $table->foreignId(
            'updated_by'
        )->nullable();

        $table->foreignId(
            'posted_by'
        )->nullable();

        $table->timestamp(
            'posted_at'
        )->nullable();

        $table->foreignId(
            'cancelled_by'
        )->nullable();

        $table->timestamp(
            'cancelled_at'
        )->nullable();

        $table->text(
            'cancel_reason'
        )->nullable();
        $table->foreignId(
            'completed_by'
        )->nullable();

        $table->timestamp(
            'completed_at'
        )->nullable();

        $table->timestamps();

    }

);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
