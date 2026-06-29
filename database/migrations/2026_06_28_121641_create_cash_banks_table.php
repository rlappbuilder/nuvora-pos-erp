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

            'cash_banks',

            function (

                Blueprint $table

            ) {

                $table->id();

                $table->string(

                    'code'

                )

                ->unique();

                $table->string(

                    'name'

                );

                $table->enum(

                    'type',

                    [

                        'Cash',

                        'Bank',

                        'E-Wallet',

                    ]

                );

                $table->string(

                    'bank_name'

                )

                ->nullable();

                $table->string(

                    'account_number'

                )

                ->nullable();

                $table->string(

                    'account_holder'

                )

                ->nullable();

                $table->decimal(

                    'opening_balance',

                    18,

                    2

                )

                ->default(

                    0

                );

                $table->decimal(

                    'current_balance',

                    18,

                    2

                )

                ->default(

                    0

                );

                $table->boolean(

                    'is_active'

                )

                ->default(

                    true

                );

                $table->text(

                    'remarks'

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(

            'cash_banks'

        );
    }
};