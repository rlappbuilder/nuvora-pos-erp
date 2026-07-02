<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(

            'cash_banks',

            function (

                Blueprint $table

            ) {

                $table->foreignId(

                    'company_id'

                )

                ->nullable()

                ->after('id')

                ->constrained()

                ->nullOnDelete();

                $table->foreignId(

                    'branch_id'

                )

                ->nullable()

                ->after('company_id')

                ->constrained()

                ->nullOnDelete();

                $table->unsignedBigInteger(

                    'coa_id'

                )

                ->nullable()

                ->after('branch_id');

                $table->string(

                    'bank_branch'

                )

                ->nullable()

                ->after('bank_name');

                $table->text(

                    'description'

                )

                ->nullable()

                ->after('current_balance');

                $table->boolean(

                    'status'

                )

                ->default(true)

                ->after('description');

            }

        );
    }

    public function down(): void
    {
        Schema::table(

            'cash_banks',

            function (

                Blueprint $table

            ) {

                $table->dropConstrainedForeignId(

                    'company_id'

                );

                $table->dropConstrainedForeignId(

                    'branch_id'

                );

                $table->dropColumn([

                    'coa_id',

                    'bank_branch',

                    'description',

                    'status'

                ]);

            }

        );
    }
};