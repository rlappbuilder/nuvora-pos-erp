<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('purchase_order_details');

        Schema::dropIfExists('purchase_orders');
    }

    public function down(): void
    {
        // Intentionally left empty.
        // Old purchase order structure will be recreated
        // by the new purchase order migrations.
    }
};