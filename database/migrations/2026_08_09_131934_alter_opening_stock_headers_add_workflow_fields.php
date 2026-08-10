<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opening_stock_headers', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table->timestamp('posted_at')
                ->nullable()
                ->after('status');

            $table->unsignedBigInteger('posted_by')
                ->nullable()
                ->after('posted_at');

            $table->timestamp('rejected_at')
                ->nullable()
                ->after('posted_by');

            $table->unsignedBigInteger('rejected_by')
                ->nullable()
                ->after('rejected_at');

            $table->text('rejected_reason')
                ->nullable()
                ->after('rejected_by');

            /*
            |--------------------------------------------------------------------------
            | Delete Audit
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('deleted_by')
                ->nullable()
                ->after('updated_by');

            $table->text('deleted_reason')
                ->nullable()
                ->after('deleted_by');

            /*
            |--------------------------------------------------------------------------
            | Soft Delete
            |--------------------------------------------------------------------------
            */

            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | Change Status
        |--------------------------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE opening_stock_headers
            MODIFY status ENUM(
                'Draft',
                'Rejected',
                'Posted'
            ) NOT NULL DEFAULT 'Draft'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE opening_stock_headers
            MODIFY status ENUM(
                'Draft',
                'Posted',
                'Cancelled'
            ) NOT NULL DEFAULT 'Draft'
        ");

        Schema::table('opening_stock_headers', function (Blueprint $table) {

            $table->dropColumn([
                'posted_at',
                'posted_by',
                'rejected_at',
                'rejected_by',
                'rejected_reason',
                'deleted_by',
                'deleted_reason',
                'deleted_at',
            ]);

        });
    }
};