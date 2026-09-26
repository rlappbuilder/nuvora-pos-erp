<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
               Schema::create('pos_sales', function (Blueprint $table) {
                $table->id();

                $table->foreignId('company_id')
                    ->constrained('companies')
                    ->cascadeOnDelete();

                $table->foreignId('branch_id')
                    ->constrained('branches')
                    ->cascadeOnDelete();

                $table->foreignId('warehouse_id')
                    ->constrained('warehouses')
                    ->restrictOnDelete();

                $table->foreignId('cashier_session_id')
                    ->constrained('cashier_sessions')
                    ->restrictOnDelete();

                $table->foreignId('customer_id')
                    ->nullable()
                    ->constrained('customers')
                    ->nullOnDelete();

                $table->string('sale_number', 50);

                $table->dateTime('sale_date');

                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('discount_amount', 15, 2)->default(0);
                $table->decimal('grand_total', 15, 2)->default(0);

                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->decimal('change_amount', 15, 2)->default(0);

                $table->enum('status', [
                    'posted',
                    'voided',
                ])->default('posted');

                $table->text('note')->nullable();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->foreignId('updated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();

                $table->unique('sale_number');

                $table->index(['company_id', 'branch_id']);
                $table->index('warehouse_id');
                $table->index('cashier_session_id');
                $table->index('customer_id');
                $table->index('sale_date');
                $table->index(['branch_id', 'sale_date']);
                $table->index('status');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sales');
    }
};
