<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_return_headers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignId('stock_transfer_header_id')
                ->constrained('stock_transfer_headers')
                ->restrictOnDelete();

            // Cabang penerima transfer asal yang melakukan retur.
            $table->foreignId('from_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('from_warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            // Cabang pengirim transfer asal yang menerima barang retur.
            $table->foreignId('to_branch_id')
                ->constrained('branches')
                ->restrictOnDelete();

            $table->foreignId('to_warehouse_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->string('number', 50)->unique();
            $table->date('transaction_date');

            $table->enum('status', [
                'Draft',
                'Submitted',
                'Approved',
                'Posted',
                'Rejected',
                'Cancelled',
            ])->default('Draft');

            $table->text('description')->nullable();

           $table->foreignId('source_journal_entry_id')
    ->nullable()
    ->constrained('journal_entries', 'id', 'strh_source_journal_fk')
    ->restrictOnDelete();

$table->foreignId('destination_journal_entry_id')
    ->nullable()
    ->constrained('journal_entries', 'id', 'strh_destination_journal_fk')
    ->restrictOnDelete();

            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('rejected_reason')->nullable();

            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('cancelled_reason')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('deleted_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_return_headers');
    }
};