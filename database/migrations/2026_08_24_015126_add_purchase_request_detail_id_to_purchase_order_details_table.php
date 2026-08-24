<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'purchase_order_details',
            function (Blueprint $table) {

                $table
                    ->foreignId(
                        'purchase_request_detail_id'
                    )
                    ->nullable()
                    ->after(
                        'purchase_order_id'
                    )
                    ->constrained(
                        'purchase_request_details'
                    )
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $table->index(
                    'purchase_request_detail_id',
                    'po_details_pr_detail_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'purchase_order_details',
            function (Blueprint $table) {

                $table->dropForeign([
                    'purchase_request_detail_id',
                ]);

                $table->dropIndex(
                    'po_details_pr_detail_idx'
                );

                $table->dropColumn(
                    'purchase_request_detail_id'
                );
            }
        );
    }
};