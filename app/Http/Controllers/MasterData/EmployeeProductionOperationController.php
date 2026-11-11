<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Production\EmployeeProductionOperation\StoreEmployeeProductionOperationRequest;
use App\Http\Requests\Production\EmployeeProductionOperation\UpdateEmployeeProductionOperationRequest;
use App\Http\Requests\Production\EmployeeProductionOperation\StoreEmployeeProductionOperationRateRequest;
use App\Http\Requests\Production\EmployeeProductionOperation\UpdateEmployeeProductionOperationRateRequest;
use App\Models\MasterData\Employee;
use App\Models\Product\ProductVariant;
use App\Models\Production\EmployeeProductionOperation;
use App\Models\Production\EmployeeProductionOperationRate;
use App\Models\Production\ProductionOperation;
use App\Services\Production\EmployeeProductionOperationService;
use Illuminate\Http\JsonResponse;

class EmployeeProductionOperationController extends Controller
{
    protected EmployeeProductionOperationService $service;

    public function __construct(
        EmployeeProductionOperationService $service
    ) {
        $this->service = $service;
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Employee $employee): JsonResponse
    {
        $companyId = auth()->user()->company_id;

        $assignments = $employee
            ->productionOperationAssignments()
            ->where('company_id', $companyId)
            ->with([
                'productionOperation:id,code,name,sort_order,is_active',
                'rates' => function ($query) {
                    $query->orderBy('id');
                },
                'rates.productVariant.product',
            ])
            ->orderBy('id')
            ->get();

        $operations = ProductionOperation::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'sort_order',
            ]);

        $productVariants = ProductVariant::query()
            ->active()
            ->whereHas('product', function ($query) {
                $query->where('is_active', true);
            })
            ->with('product:id,code,sku,name')
            ->orderBy('name')
            ->get([
                'id',
                'product_id',
                'sku',
                'barcode',
                'name',
            ]);

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'name' => $employee->name,
            ],
            'assignments' => $assignments,
            'operations' => $operations,
            'productVariants' => $productVariants,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Assignment
    |--------------------------------------------------------------------------
    */

    public function store(
        StoreEmployeeProductionOperationRequest $request,
        Employee $employee
    ): JsonResponse {

        $companyId = auth()->user()->company_id;

        $data = $request->validated();

        $data['company_id'] = $companyId;
        $data['employee_id'] = $employee->id;
        $data['created_by'] = auth()->id();

        $this->service->store($data);

        $assignment = EmployeeProductionOperation::query()
            ->where('company_id', $companyId)
            ->where('employee_id', $employee->id)
            ->where(
                'production_operation_id',
                $data['production_operation_id']
            )
            ->with([
                'productionOperation:id,code,name,sort_order,is_active',
                'rates.productVariant.product',
            ])
            ->latest('id')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Employee production operation assigned successfully.',
            'assignment' => $assignment,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Assignment
    |--------------------------------------------------------------------------
    */

    public function update(
        UpdateEmployeeProductionOperationRequest $request,
        Employee $employee,
        EmployeeProductionOperation $assignment
    ): JsonResponse {

        $companyId = auth()->user()->company_id;

        abort_unless(
            $assignment->company_id === $companyId
            && $assignment->employee_id === $employee->id,
            404
        );

        $data = $request->validated();

        unset(
            $data['company_id'],
            $data['employee_id'],
            $data['production_operation_id']
        );

        $data['updated_by'] = auth()->id();

        $this->service->update(
            $assignment,
            $data
        );

        $assignment->refresh();

        $assignment->load([
            'productionOperation:id,code,name,sort_order,is_active',
            'rates.productVariant.product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employee production operation updated successfully.',
            'assignment' => $assignment,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy Assignment
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Employee $employee,
        EmployeeProductionOperation $assignment
    ): JsonResponse {

        $companyId = auth()->user()->company_id;

        abort_unless(
            $assignment->company_id === $companyId
            && $assignment->employee_id === $employee->id,
            404
        );

        $this->service->destroy($assignment);

        return response()->json([
            'success' => true,
            'message' => 'Employee production operation deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Rate
    |--------------------------------------------------------------------------
    */

    public function storeRate(
        StoreEmployeeProductionOperationRateRequest $request,
        Employee $employee,
        EmployeeProductionOperation $assignment
    ): JsonResponse {

        $companyId = auth()->user()->company_id;

        abort_unless(
            $assignment->company_id === $companyId
            && $assignment->employee_id === $employee->id,
            404
        );

        $data = $request->validated();

        $data['created_by'] = auth()->id();

        $rate = $this->service->storeRate(
            $assignment,
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Production operation rate added successfully.',
            'rate' => $rate,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Rate
    |--------------------------------------------------------------------------
    */

    public function updateRate(
        UpdateEmployeeProductionOperationRateRequest $request,
        Employee $employee,
        EmployeeProductionOperation $assignment,
        EmployeeProductionOperationRate $rate
    ): JsonResponse {

        $companyId = auth()->user()->company_id;

        abort_unless(
            $assignment->company_id === $companyId
            && $assignment->employee_id === $employee->id
            && $rate->employee_production_operation_id === $assignment->id,
            404
        );

        $data = $request->validated();

        $data['updated_by'] = auth()->id();

        $rate = $this->service->updateRate(
            $rate,
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Production operation rate updated successfully.',
            'rate' => $rate,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy Rate
    |--------------------------------------------------------------------------
    */

    public function destroyRate(
        Employee $employee,
        EmployeeProductionOperation $assignment,
        EmployeeProductionOperationRate $rate
    ): JsonResponse {

        $companyId = auth()->user()->company_id;

        abort_unless(
            $assignment->company_id === $companyId
            && $assignment->employee_id === $employee->id
            && $rate->employee_production_operation_id === $assignment->id,
            404
        );

        $this->service->destroyRate($rate);

        return response()->json([
            'success' => true,
            'message' => 'Production operation rate deleted successfully.',
        ]);
    }
}