<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    private string $table = 'employee_production_operation_rates';

    private function indexExists(string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $this->table)
            ->where('index_name', $indexName)
            ->exists();
    }

    private function foreignKeyExists(string $foreignKey): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $this->table)
            ->where('CONSTRAINT_NAME', $foreignKey)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }

    public function up(): void
    {
        // Pastikan tarif lama belum memiliki data.
        // Product tidak bisa dipetakan otomatis ke satu variant
        // karena satu product dapat memiliki banyak variant.
        if (DB::table($this->table)->exists()) {
            throw new RuntimeException(
                'Migration dihentikan: tabel employee_production_operation_rates '
                . 'masih memiliki data tarif lama. Migrasikan data terlebih dahulu.'
            );
        }

        /*
         * STEP 1
         * Tambahkan index khusus untuk foreign key assignment.
         * Ini wajib dilakukan sebelum menghapus unique index lama.
         */
        if (!$this->indexExists('epor_operation_idx')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->index(
                    'employee_production_operation_id',
                    'epor_operation_idx'
                );
            });
        }

        /*
         * STEP 2
         * Hapus foreign key Product lama jika masih ada.
         * Pada database lokal FK ini sudah terhapus
         * akibat migration sebelumnya yang gagal.
         */
        if ($this->foreignKeyExists('epor_product_fk')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropForeign('epor_product_fk');
            });
        }

        /*
         * STEP 3
         * Hapus index lama.
         * Index assignment sudah memiliki pengganti.
         */
        if ($this->indexExists('epor_operation_product_unique')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropUnique('epor_operation_product_unique');
            });
        }

        if ($this->indexExists('epor_product_active_idx')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropIndex('epor_product_active_idx');
            });
        }

        /*
         * STEP 4
         * Hapus kolom Product lama.
         */
        if (Schema::hasColumn($this->table, 'product_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('product_id');
            });
        }

        /*
         * STEP 5
         * Tambahkan kolom Product Variant.
         */
        if (!Schema::hasColumn($this->table, 'product_variant_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unsignedBigInteger('product_variant_id')
                    ->after('employee_production_operation_id');
            });
        }

        /*
         * STEP 6
         * Tambahkan foreign key dan index baru.
         */
        if (!$this->foreignKeyExists('epor_variant_fk')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->foreign(
                    'product_variant_id',
                    'epor_variant_fk'
                )
                    ->references('id')
                    ->on('product_variants')
                    ->restrictOnDelete();
            });
        }

        if (!$this->indexExists('epor_operation_variant_unique')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unique(
                    [
                        'employee_production_operation_id',
                        'product_variant_id',
                    ],
                    'epor_operation_variant_unique'
                );
            });
        }

        if (!$this->indexExists('epor_variant_active_idx')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->index(
                    ['product_variant_id', 'is_active'],
                    'epor_variant_active_idx'
                );
            });
        }
    }

    public function down(): void
    {
        if (DB::table($this->table)->exists()) {
            throw new RuntimeException(
                'Rollback dihentikan: tabel masih memiliki data tarif Product Variant.'
            );
        }

        if ($this->foreignKeyExists('epor_variant_fk')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropForeign('epor_variant_fk');
            });
        }

        if ($this->indexExists('epor_operation_variant_unique')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropUnique('epor_operation_variant_unique');
            });
        }

        if ($this->indexExists('epor_variant_active_idx')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropIndex('epor_variant_active_idx');
            });
        }

        if (Schema::hasColumn($this->table, 'product_variant_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('product_variant_id');
            });
        }

        if (!Schema::hasColumn($this->table, 'product_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')
                    ->after('employee_production_operation_id');
            });
        }

        Schema::table($this->table, function (Blueprint $table) {
            $table->foreign(
                'product_id',
                'epor_product_fk'
            )
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->unique(
                [
                    'employee_production_operation_id',
                    'product_id',
                ],
                'epor_operation_product_unique'
            );

            $table->index(
                ['product_id', 'is_active'],
                'epor_product_active_idx'
            );
        });

        if ($this->indexExists('epor_operation_idx')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropIndex('epor_operation_idx');
            });
        }
    }
};