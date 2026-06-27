<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(

            'stock_issues',

            function (

                Blueprint $table

            ) {

                $table->id();

                $table->string(

                    'issue_number'

                )->unique();

                $table->date(

                    'issue_date'

                );

                $table->foreignId(

                    'warehouse_id'

                )

                ->constrained()

                ->cascadeOnUpdate();

                $table->string(

                    'issue_type'

                );

                $table->string(

                    'reference_number'

                )

                ->nullable();

                $table->text(

                    'remarks'

                )

                ->nullable();

                $table->decimal(

                    'total_qty',

                    18,

                    2

                )

                ->default(0);

                $table->decimal(

                    'total_cost',

                    18,

                    2

                )

                ->default(0);

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

                $table->foreignId(

                    'created_by'

                )

                ->nullable()

                ->constrained(

                    'users'

                )

                ->nullOnDelete();

                $table->foreignId(

                    'updated_by'

                )

                ->nullable()

                ->constrained(

                    'users'

                )

                ->nullOnDelete();

                $table->timestamps();

            }

        );
    }

    public function down(): void
    {
        Schema::dropIfExists(

            'stock_issues'

        );
    }
};