<?php

namespace Database\Seeders\Production;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Production\ProductionOperation;

class ProductionOperationSeeder extends Seeder
{
    public function run(): void
    {
        $operations = [
            [
                'code' => 'OPR0001',
                'name' => 'Cutting',
                'description' => 'Fabric cutting operation.',
                'sort_order' => 1,
            ],
            [
                'code' => 'OPR0002',
                'name' => 'Sewing',
                'description' => 'Garment sewing operation.',
                'sort_order' => 2,
            ],
            [
                'code' => 'OPR0003',
                'name' => 'Overlock',
                'description' => 'Fabric edge overlock operation.',
                'sort_order' => 3,
            ],
            [
                'code' => 'OPR0004',
                'name' => 'Ironing',
                'description' => 'Garment ironing operation.',
                'sort_order' => 4,
            ],
            [
                'code' => 'OPR0005',
                'name' => 'Finishing',
                'description' => 'Garment finishing and quality preparation.',
                'sort_order' => 5,
            ],
            [
                'code' => 'OPR0006',
                'name' => 'Packing',
                'description' => 'Finished garment packing operation.',
                'sort_order' => 6,
            ],
        ];

        DB::transaction(function () use ($operations) {
            $companies = DB::table('companies')
                ->select('id')
                ->get();

            foreach ($companies as $company) {
                foreach ($operations as $operation) {
                    ProductionOperation::withTrashed()->updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'code' => $operation['code'],
                        ],
                        [
                            'name' => $operation['name'],
                            'description' => $operation['description'],
                            'sort_order' => $operation['sort_order'],
                            'is_active' => true,
                            'deleted_at' => null,
                            'deleted_by' => null,
                        ]
                    );
                }
            }
        });
    }
}