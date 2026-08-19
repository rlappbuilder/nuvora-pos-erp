<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Drop Old Stock Issue Details
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists(
            'stock_issue_details'
        );


        /*
        |--------------------------------------------------------------------------
        | Drop Old Stock Issues
        |--------------------------------------------------------------------------
        */

        Schema::dropIfExists(
            'stock_issues'
        );
    }


    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Intentionally Empty
        |--------------------------------------------------------------------------
        |
        | Old Stock Issue tables are being replaced by the new
        | stock_issue_headers / stock_issue_details structure.
        |
        */

    }
};