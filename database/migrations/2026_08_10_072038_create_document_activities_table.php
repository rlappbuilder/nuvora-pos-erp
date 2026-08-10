<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_activities', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Document
            |--------------------------------------------------------------------------
            |
            | Generic document reference.
            |
            | Example:
            | OpeningStock + ID 4
            | PurchaseOrder + ID 10
            | SalesInvoice + ID 25
            |
            */

            $table->string('document_type', 100);

            $table->unsignedBigInteger('document_id');

            /*
            |--------------------------------------------------------------------------
            | Activity
            |--------------------------------------------------------------------------
            */

            $table->string('action', 50);

            $table->string('old_status', 50)
                ->nullable();

            $table->string('new_status', 50)
                ->nullable();

            $table->text('description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            |
            | Extra information depending on activity.
            |
            | Example:
            | {
            |     "reason": "Qty tidak sesuai",
            |     "old_qty": 10,
            |     "new_qty": 15
            | }
            |
            */

            $table->json('metadata')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('performed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('performed_at');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'document_type',
                'document_id',
            ]);

            $table->index([
                'company_id',
                'document_type',
                'document_id',
            ]);

            $table->index('action');

            $table->index('performed_at');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'document_activities'
        );
    }
};