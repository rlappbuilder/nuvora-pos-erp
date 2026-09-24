<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\EmployeeRequest;
use App\Models\MasterData\Employee;
use App\Services\MasterData\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    protected EmployeeService $employeeService;

    public function __construct(
        EmployeeService $employeeService
    ) {
        $this->employeeService =
            $employeeService;
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Employee::query()
            ->with([
                'user',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'employee_code',
            'name',
            'email',
            'position',
            'join_date',
            'status',
            'created_at',
        ];

        $sort = $request->get(
            'sort',
            'employee_code'
        );

        $direction = $request->get(
            'direction',
            'asc'
        );

        if (! in_array(
            $sort,
            $allowedSorts
        )) {
            $sort = 'employee_code';
        }

        if (! in_array(
            $direction,
            ['asc', 'desc']
        )) {
            $direction = 'asc';
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->search,
            function ($query, $search) {

                $query->where(function ($query) use (
                    $search
                ) {
                    $query
                        ->where(
                            'employee_code',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'name',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'email',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'position',
                            'like',
                            '%' . $search . '%'
                        );
                });
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled('status'),
            function ($query) use ($request) {

                $query->where(
                    'status',
                    $request->status
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Sort
        |--------------------------------------------------------------------------
        */

        $query->orderBy(
            $sort,
            $direction
        );

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $employees = (clone $query)
            ->paginate(
                $request->integer(
                    'per_page',
                    10
                )
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total' => (clone $query)
                ->count(),

            'active' => (clone $query)
                ->where(
                    'status',
                    true
                )
                ->count(),

            'inactive' => (clone $query)
                ->where(
                    'status',
                    false
                )
                ->count(),

            'assigned' => (clone $query)
                ->whereNotNull(
                    'user_id'
                )
                ->count(),

            'unassigned' => (clone $query)
                ->whereNull(
                    'user_id'
                )
                ->count(),

            'deleted' => Employee::onlyTrashed()
                ->count(),
        ];

        return Inertia::render(
            'MasterData/Employee/Index',
            [
                'employees' => $employees,

                'stats' => $stats,

                'filters' => [
                    'search' =>
                        $request->search,

                    'status' =>
                        $request->status,

                    'per_page' =>
                        $request->integer(
                            'per_page',
                            10
                        ),

                    'sort' =>
                        $sort,

                    'direction' =>
                        $direction,
                ],
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Code
    |--------------------------------------------------------------------------
    */

    public function previewCode(): JsonResponse
    {
        return response()->json([
            'code' =>
                $this->employeeService
                    ->previewCode(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        EmployeeRequest $request
    ) {
        $this->employeeService->create(
            $request->validated()
        );

        return redirect()
            ->route(
                'employees.index'
            )
            ->with(
                'success',
                'Employee created successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

   public function show(Employee $employee)
    {
        $employee->load([
            'user',
        ]);

        return Inertia::render(
            'MasterData/Employee/Show',
            [
                'employee' => $employee,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        EmployeeRequest $request,
        Employee $employee
    ) {
        $this->employeeService->update(
            $employee,
            $request->validated()
        );

        return redirect()
            ->route(
                'employees.index'
            )
            ->with(
                'success',
                'Employee updated successfully.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Employee $employee
    ) {
        $this->employeeService->delete(
            $employee
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Employee deleted successfully.'
            );
    }
}