<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inter_branch_settlement_details', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Primary
            |--------------------------------------------------------------------------
            */

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Settlement Header
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger(
                'inter_branch_settlement_header_id'
            );

            $table->foreign(
                'inter_branch_settlement_header_id',
                'ibs_det_header_fk'
            )
                ->references('id')
                ->on('inter_branch_settlement_headers')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Source Fund Transfer
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger(
                'fund_transfer_header_id'
            );

            $table->foreign(
                'fund_transfer_header_id',
                'ibs_det_transfer_fk'
            )
                ->references('id')
                ->on('fund_transfer_headers')
                ->restrictOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Settlement Amount
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 15, 2);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'inter_branch_settlement_header_id',
                    'fund_transfer_header_id',
                ],
                'ibs_det_header_transfer_idx'
            );

            $table->index(
                'fund_transfer_header_id',
                'ibs_det_transfer_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'inter_branch_settlement_details'
        );
    }
};