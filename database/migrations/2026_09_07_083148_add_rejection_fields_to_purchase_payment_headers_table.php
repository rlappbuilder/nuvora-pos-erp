<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'purchase_payment_headers',
            function (Blueprint $table) {

                $table->enum(
                    'status',
                    [
                        'Draft',
                        'Submitted',
                        'Rejected',
                        'Approved',
                        'Posted',
                        'Cancelled',
                    ]
                )->default('Draft')->change();

                $table->timestamp('rejected_at')
                    ->nullable()
                    ->after('approved_by');

                $table->foreignId('rejected_by')
                    ->nullable()
                    ->after('rejected_at')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->text('reject_reason')
                    ->nullable()
                    ->after('rejected_by');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'purchase_payment_headers',
            function (Blueprint $table) {

                $table->dropForeign([
                    'rejected_by',
                ]);

                $table->dropColumn([
                    'rejected_at',
                    'rejected_by',
                    'reject_reason',
                ]);

                $table->enum(
                    'status',
                    [
                        'Draft',
                        'Submitted',
                        'Approved',
                        'Posted',
                        'Cancelled',
                    ]
                )->default('Draft')->change();
            }
        );
    }
};