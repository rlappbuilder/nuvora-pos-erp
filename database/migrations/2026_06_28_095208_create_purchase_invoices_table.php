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
    Schema::create(

        'purchase_invoices',

        function (

            Blueprint $table

        ) {

            $table->id();

            $table->string(

                'invoice_number'

            )

            ->unique();

            $table->foreignId(

                'supplier_id'

            )

            ->constrained()

            ->cascadeOnDelete();

            $table->foreignId(

                'goods_receipt_id'

            )

            ->constrained()

            ->cascadeOnDelete();

            $table->date(

                'invoice_date'

            );

            $table->date(

                'due_date'

            );

            $table->string(

                'reference_number'

            )

            ->nullable();

            $table->decimal(

                'subtotal',

                18,

                2

            )

            ->default(

                0

            );

            $table->decimal(

                'discount',

                18,

                2

            )

            ->default(

                0

            );

            $table->decimal(

                'tax',

                18,

                2

            )

            ->default(

                0

            );

            $table->decimal(

                'grand_total',

                18,

                2

            )

            ->default(

                0

            );

            $table->text(

                'remarks'

            )

            ->nullable();

            $table->enum(

                'status',

                [

                    'Draft',

                    'Posted',

                    'Completed',

                    'Cancelled',

                ]

            )

            ->default(

                'Draft'

            );

            $table->foreignId(

                'created_by'

            )

            ->nullable()

            ->constrained(

                'users'

            )

            ->nullOnDelete();

            $table->foreignId(

                'posted_by'

            )

            ->nullable()

            ->constrained(

                'users'

            )

            ->nullOnDelete();

            $table->timestamp(

                'posted_at'

            )

            ->nullable();

            $table->foreignId(

                'completed_by'

            )

            ->nullable()

            ->constrained(

                'users'

            )

            ->nullOnDelete();

            $table->timestamp(

                'completed_at'

            )

            ->nullable();

            $table->foreignId(

                'cancelled_by'

            )

            ->nullable()

            ->constrained(

                'users'

            )

            ->nullOnDelete();

            $table->timestamp(

                'cancelled_at'

            )

            ->nullable();

            $table->text(

                'cancel_reason'

            )

            ->nullable();

            $table->timestamps();

        }

    );
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::dropIfExists(

        'purchase_invoices'

    );
}
};
