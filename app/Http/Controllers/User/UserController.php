<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Models\MasterData\Branch;
use App\Models\MasterData\Company;
use App\Models\MasterData\Employee;
use App\Models\User;
use App\Services\User\UserBranchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected UserBranchService $userBranchService;

    public function __construct(
        UserBranchService $userBranchService
    ) {
        $this->userBranchService = $userBranchService;
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $users = User::query()
            ->with([
                'company',
                'employee',
                'roles',
                'branches',
            ])
            ->when(
                $request->search,
                function ($query, $search) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'email',
                                'like',
                                '%' . $search . '%'
                            );
                    });
                }
            )
            ->latest()
            ->paginate(
                $request->integer(
                    'per_page',
                    10
                )
            )
            ->withQueryString();

        $users->getCollection()
            ->transform(function ($user) {
                $user->default_branch =
                    $user->branches
                        ->first(
                            fn ($branch) =>
                                $branch->pivot->is_default
                        );

                return $user;
            });

        $formData = $this->formData();

        return Inertia::render(
            'User/Index',
            array_merge(
                [
                    'users' => $users,

                    'filters' => [
                        'search' => $request->search,
                        'per_page' => $request->integer(
                            'per_page',
                            10
                        ),
                    ],
                ],
                $formData
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Form Data
    |--------------------------------------------------------------------------
    */

    protected function formData(): array
    {
        return [
            'companies' => Company::query()
                ->where('status', true)
                ->orderBy('company_name')
                ->get([
                    'id',
                    'company_name',
                ]),

            'branches' => Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'company_id',
                    'code',
                    'name',
                ]),

            'roles' => Role::query()
                ->where(
                    'guard_name',
                    'web'
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ]),

            'employees' => Employee::query()
                ->where('status', true)
                ->whereNull('user_id')
                ->orderBy('name')
                ->get([
                    'id',
                    'employee_code',
                    'name',
                    'email',
                ]),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(UserRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Employee
            |--------------------------------------------------------------------------
            */

            $employee = Employee::query()
                ->where('id', $data['employee_id'])
                ->where('status', true)
                ->whereNull('user_id')
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Create User
            |--------------------------------------------------------------------------
            */

            $user = User::create([
                'company_id' => $data['company_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Connect Employee
            |--------------------------------------------------------------------------
            */

            $employee->update([
                'user_id' => $user->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Role
            |--------------------------------------------------------------------------
            */

            $user->assignRole(
                $data['role']
            );

            /*
            |--------------------------------------------------------------------------
            | Branch Access
            |--------------------------------------------------------------------------
            */

            $this->userBranchService->sync(
                $user,
                $data['branch_ids']
            );

            /*
            |--------------------------------------------------------------------------
            | Default Branch
            |--------------------------------------------------------------------------
            */

            $defaultBranch = Branch::findOrFail(
                $data['default_branch_id']
            );

            $this->userBranchService->setDefault(
                $user,
                $defaultBranch
            );

            return redirect()
                ->route('users.index')
                ->with(
                    'success',
                    'User created successfully.'
                );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(User $user)
    {
        $user->load([
            'company',
            'employee',
            'roles',
            'branches',
        ]);

        $user->default_branch =
            $user->branches
                ->first(
                    fn ($branch) =>
                        $branch->pivot->is_default
                );

        return Inertia::render(
            'User/Show',
            [
                'user' => $user,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        UserRequest $request,
        User $user
    ) {
        $data = $request->validated();

        return DB::transaction(function () use (
            $data,
            $user
        ) {

            /*
            |--------------------------------------------------------------------------
            | Employee
            |--------------------------------------------------------------------------
            */

            $currentEmployee = $user->employee;

            if (
                ! $currentEmployee ||
                (int) $currentEmployee->id !==
                    (int) $data['employee_id']
            ) {

                /*
                |--------------------------------------------------------------------------
                | Release Current Employee
                |--------------------------------------------------------------------------
                */

                if ($currentEmployee) {
                    $currentEmployee->update([
                        'user_id' => null,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Connect New Employee
                |--------------------------------------------------------------------------
                */

                $newEmployee = Employee::query()
                    ->where(
                        'id',
                        $data['employee_id']
                    )
                    ->where(
                        'status',
                        true
                    )
                    ->where(function ($query) use ($user) {
                        $query
                            ->whereNull('user_id')
                            ->orWhere(
                                'user_id',
                                $user->id
                            );
                    })
                    ->lockForUpdate()
                    ->firstOrFail();

                $newEmployee->update([
                    'user_id' => $user->id,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | User Data
            |--------------------------------------------------------------------------
            */

            $updateData = [
                'company_id' => $data['company_id'],
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (
                filled($data['password'] ?? null)
            ) {
                $updateData['password'] =
                    $data['password'];
            }

            $user->update(
                $updateData
            );

            /*
            |--------------------------------------------------------------------------
            | Role
            |--------------------------------------------------------------------------
            */

            $user->syncRoles([
                $data['role'],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Branch Access
            |--------------------------------------------------------------------------
            */

            $this->userBranchService->sync(
                $user,
                $data['branch_ids']
            );

            /*
            |--------------------------------------------------------------------------
            | Default Branch
            |--------------------------------------------------------------------------
            */

            $defaultBranch = Branch::findOrFail(
                $data['default_branch_id']
            );

            $this->userBranchService->setDefault(
                $user,
                $defaultBranch
            );

            return redirect()
                ->route(
                    'users.index'
                )
                ->with(
                    'success',
                    'User updated successfully.'
                );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()
            ->back()
            ->with(
                'success',
                'User deleted successfully.'
            );
    }
}