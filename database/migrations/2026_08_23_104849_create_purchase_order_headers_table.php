<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_headers', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->id();


            /*
            |--------------------------------------------------------------------------
            | Company / Location
            |--------------------------------------------------------------------------
            */

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnUpdate()
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Supplier
            |--------------------------------------------------------------------------
            */

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Purchase Request Reference
            |--------------------------------------------------------------------------
            */

            $table->foreignId('purchase_request_id')
                ->nullable()
                ->constrained('purchase_request_headers')
                ->cascadeOnUpdate()
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Document
            |--------------------------------------------------------------------------
            */

            $table->string('number', 50)
                ->unique();

            $table->date('order_date');

            $table->date('required_date')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'Draft',

                'Submitted',

                'Rejected',

                'Approved',

                'Sent',

                'Confirmed',

                'Partially Received',

                'Fully Received',

                'Cancelled',

                'Closed',

            ])->default('Draft');


            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            $table->timestamp('approved_at')
                ->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Rejection
            |--------------------------------------------------------------------------
            */

            $table->timestamp('rejected_at')
                ->nullable();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('rejected_reason')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Supplier Communication
            |--------------------------------------------------------------------------
            */

            $table->timestamp('sent_at')
                ->nullable();

            $table->foreignId('sent_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('confirmed_at')
                ->nullable();

            $table->foreignId('confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Cancellation
            |--------------------------------------------------------------------------
            */

            $table->timestamp('cancelled_at')
                ->nullable();

            $table->foreignId('cancelled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('cancelled_reason')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Receiving Summary
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'total_quantity',
                18,
                6
            )->default(0);

            $table->decimal(
                'received_quantity',
                18,
                6
            )->default(0);

            $table->decimal(
                'remaining_quantity',
                18,
                6
            )->default(0);


            /*
            |--------------------------------------------------------------------------
            | Amount Summary
            |--------------------------------------------------------------------------
            */

            $table->decimal(
                'subtotal',
                18,
                2
            )->default(0);

            $table->decimal(
                'discount_amount',
                18,
                2
            )->default(0);

            $table->decimal(
                'tax_amount',
                18,
                2
            )->default(0);

            $table->decimal(
                'grand_total',
                18,
                2
            )->default(0);


            /*
            |--------------------------------------------------------------------------
            | Information
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

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

            $table->text('deleted_reason')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | Timestamps / Soft Delete
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'company_id',
                'branch_id',
            ]);

            $table->index([
                'supplier_id',
                'status',
            ]);

            $table->index(
                'purchase_request_id'
            );

            $table->index(
                'order_date'
            );

            $table->index(
                'status'
            );

        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'purchase_order_headers'
        );
    }
};