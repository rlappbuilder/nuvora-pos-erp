<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(

            'inventory_adjustments',

            function (

                Blueprint $table

            ) {

                $table->id();

                $table->string(
                    'adjustment_number'
                )->unique();

                $table->foreignId(
                    'warehouse_id'
                );

                $table->date(
                    'adjustment_date'
                );

                $table->string(
                    'status'
                )
                ->default(
                    'Draft'
                );

                $table->text(
                    'remarks'
                )
                ->nullable();

                $table->foreignId(
                    'created_by'
                )
                ->nullable();

                $table->foreignId(
                    'updated_by'
                )
                ->nullable();

                $table->timestamp(
                    'posted_at'
                )
                ->nullable();

                $table->foreignId(
                    'posted_by'
                )
                ->nullable();

                $table->timestamp(
                    'cancelled_at'
                )
                ->nullable();

                $table->foreignId(
                    'cancelled_by'
                )
                ->nullable();

                $table->text(
                    'cancel_reason'
                )
                ->nullable();

                $table->timestamps();

            }

        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'inventory_adjustments'
        );
    }
};