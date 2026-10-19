<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfer_headers', function (Blueprint $table) {
            $table->foreignId('source_journal_entry_id')
                ->nullable()
                ->after('description')
                ->constrained('journal_entries')
                ->restrictOnDelete();

            $table->foreignId('destination_journal_entry_id')
                ->nullable()
                ->after('source_journal_entry_id')
                ->constrained('journal_entries')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfer_headers', function (Blueprint $table) {
            $table->dropForeign(['source_journal_entry_id']);
            $table->dropForeign(['destination_journal_entry_id']);

            $table->dropColumn([
                'source_journal_entry_id',
                'destination_journal_entry_id',
            ]);
        });
    }
};