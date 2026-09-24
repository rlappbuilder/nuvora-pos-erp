<?php

namespace App\Services\MasterData;

use App\Models\MasterData\Employee;
use App\Services\Core\CodeGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    protected CodeGeneratorService $codeGeneratorService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService
    ) {
        $this->codeGeneratorService =
            $codeGeneratorService;
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(array $data): Employee
    {
        return DB::transaction(function () use ($data) {

            $data['employee_code'] =
                $this->codeGeneratorService
                    ->next('employee');

            $data['created_by'] =
                auth()->id();

            $data['updated_by'] =
                auth()->id();

            return Employee::create($data);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Employee $employee,
        array $data
    ): Employee {
        return DB::transaction(function () use (
            $employee,
            $data
        ) {

            $data['updated_by'] =
                auth()->id();

            $employee->update($data);

            return $employee->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        Employee $employee
    ): void {
        if ($employee->user_id !== null) {
            throw ValidationException::withMessages([
                'employee' =>
                    'Employee cannot be deleted because it is linked to a user account.',
            ]);
        }

        DB::transaction(function () use ($employee) {
            $employee->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Code
    |--------------------------------------------------------------------------
    */

    public function previewCode(): string
    {
        return $this->codeGeneratorService
            ->preview('employee');
    }
}