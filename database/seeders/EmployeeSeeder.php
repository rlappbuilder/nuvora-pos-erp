<?php

namespace Database\Seeders;

use App\Models\MasterData\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'employee_code' => 'EMP-0001',
                'name' => 'Andi Pratama',
                'phone' => '081234567801',
                'email' => 'andi@nuvora.local',
                'position' => 'Manager',
                'join_date' => '2026-01-01',
                'user_id' => null,
                'status' => true,
            ],
            [
                'employee_code' => 'EMP-0002',
                'name' => 'Budi Santoso',
                'phone' => '081234567802',
                'email' => 'budi@nuvora.local',
                'position' => 'Cashier',
                'join_date' => '2026-01-05',
                'user_id' => null,
                'status' => true,
            ],
            [
                'employee_code' => 'EMP-0003',
                'name' => 'Citra Lestari',
                'phone' => '081234567803',
                'email' => 'citra@nuvora.local',
                'position' => 'Accounting',
                'join_date' => '2026-01-10',
                'user_id' => null,
                'status' => true,
            ],
            [
                'employee_code' => 'EMP-0004',
                'name' => 'Dedi Saputra',
                'phone' => '081234567804',
                'email' => 'dedi@nuvora.local',
                'position' => 'Warehouse',
                'join_date' => '2026-01-15',
                'user_id' => null,
                'status' => true,
            ],
        ];

        foreach ($employees as $employee) {
            Employee::updateOrCreate(
                [
                    'employee_code' => $employee['employee_code'],
                ],
                $employee
            );
        }
    }
}