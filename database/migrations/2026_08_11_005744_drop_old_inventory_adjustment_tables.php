<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists(
            'inventory_adjustment_details'
        );

        Schema::dropIfExists(
            'inventory_adjustments'
        );
    }

    public function down(): void
    {
        // Intentionally left empty.
        // Old inventory adjustment tables
        // will be recreated by the new migrations.
    }
};