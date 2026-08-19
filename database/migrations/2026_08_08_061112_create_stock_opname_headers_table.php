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
    Schema::create('stock_opname_headers', function (Blueprint $table) {

        $table->id();

        /*
        |--------------------------------------------------------------------------
        | Identity
        |--------------------------------------------------------------------------
        */

        $table->foreignId('company_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('branch_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('warehouse_id')
            ->constrained()
            ->cascadeOnDelete();


        /*
        |--------------------------------------------------------------------------
        | Document
        |--------------------------------------------------------------------------
        */

        $table->string('number', 100)
            ->unique();

        $table->date('transaction_date');

        $table->string('status', 20)
            ->default('Draft');


        /*
        |--------------------------------------------------------------------------
        | Information
        |--------------------------------------------------------------------------
        */

        $table->text('description')
            ->nullable();


        /*
        |--------------------------------------------------------------------------
        | Workflow Audit
        |--------------------------------------------------------------------------
        */

        $table->unsignedBigInteger('created_by')
            ->nullable();

        $table->unsignedBigInteger('updated_by')
            ->nullable();

        $table->unsignedBigInteger('posted_by')
            ->nullable();

        $table->timestamp('posted_at')
            ->nullable();

        $table->unsignedBigInteger('rejected_by')
            ->nullable();

        $table->timestamp('rejected_at')
            ->nullable();

        $table->text('rejected_reason')
            ->nullable();

        $table->unsignedBigInteger('deleted_by')
            ->nullable();


        /*
        |--------------------------------------------------------------------------
        | Timestamps
        |--------------------------------------------------------------------------
        */

        $table->timestamps();

        $table->softDeletes();


        /*
        |--------------------------------------------------------------------------
        | Index
        |--------------------------------------------------------------------------
        */

        $table->index(
            [
                'company_id',
                'branch_id',
                'warehouse_id',
            ],
            'soh_location_idx'
        );

        $table->index(
            'transaction_date',
            'soh_date_idx'
        );

        $table->index(
            'status',
            'soh_status_idx'
        );

    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_headers');
    }
};
