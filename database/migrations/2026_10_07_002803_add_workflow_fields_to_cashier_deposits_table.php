<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashier_deposits', function (Blueprint $table) {
            $table->foreignId('submitted_by')
                ->nullable()
                ->after('updated_by')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('submitted_at')
                ->nullable()
                ->after('submitted_by');

            $table->foreignId('approved_by')
                ->nullable()
                ->after('submitted_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')
                ->nullable()
                ->after('approved_by');

            $table->foreignId('rejected_by')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')
                ->nullable()
                ->after('rejected_by');

            $table->text('rejected_reason')
                ->nullable()
                ->after('rejected_at');

            $table->foreignId('received_by')
                ->nullable()
                ->after('rejected_reason')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('received_at')
                ->nullable()
                ->after('received_by');

            $table->foreignId('posted_by')
                ->nullable()
                ->after('received_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')
                ->nullable()
                ->after('posted_by');

            $table->foreignId('cancelled_by')
                ->nullable()
                ->after('posted_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('cancelled_at')
                ->nullable()
                ->after('cancelled_by');

            $table->text('cancelled_reason')
                ->nullable()
                ->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('cashier_deposits', function (Blueprint $table) {
            $table->dropForeign(['submitted_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropForeign(['received_by']);
            $table->dropForeign(['posted_by']);
            $table->dropForeign(['cancelled_by']);

            $table->dropColumn([
                'submitted_by',
                'submitted_at',
                'approved_by',
                'approved_at',
                'rejected_by',
                'rejected_at',
                'rejected_reason',
                'received_by',
                'received_at',
                'posted_by',
                'posted_at',
                'cancelled_by',
                'cancelled_at',
                'cancelled_reason',
            ]);
        });
    }
};