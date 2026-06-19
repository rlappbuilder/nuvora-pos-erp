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
    Schema::table(
        'purchase_orders',
        function (
            Blueprint $table
        ) {

            $table->timestamp(
                'submitted_at'
            )->nullable();

            $table->unsignedBigInteger(
                'submitted_by'
            )->nullable();

            $table->timestamp(
                'approved_at'
            )->nullable();

            $table->unsignedBigInteger(
                'approved_by'
            )->nullable();

            $table->timestamp(
                'rejected_at'
            )->nullable();

            $table->unsignedBigInteger(
                'rejected_by'
            )->nullable();

            $table->text(
                'rejection_reason'
            )->nullable();

            $table->timestamp(
                'cancelled_at'
            )->nullable();

            $table->unsignedBigInteger(
                'cancelled_by'
            )->nullable();

            $table->text(
                'cancel_reason'
            )->nullable();

        }
    );
}

public function down(): void
{
    Schema::table(
        'purchase_orders',
        function (
            Blueprint $table
        ) {

            $table->dropColumn([

                'submitted_at',
                'submitted_by',

                'approved_at',
                'approved_by',

                'rejected_at',
                'rejected_by',

                'rejection_reason',

                'cancelled_at',
                'cancelled_by',

                'cancel_reason',

            ]);

        }
    );
}
};
