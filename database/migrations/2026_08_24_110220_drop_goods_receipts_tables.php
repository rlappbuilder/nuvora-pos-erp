<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up(): void
{
    Schema::dropIfExists('supplier_payments');

    Schema::dropIfExists('purchase_invoice_details');

    Schema::dropIfExists('purchase_invoices');

    Schema::dropIfExists('goods_receipt_details');

    Schema::dropIfExists('goods_receipts');
}
    public function down(): void
    {
        //
    }
};