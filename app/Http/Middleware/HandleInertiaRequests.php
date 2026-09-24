<?php

namespace App\Http\Middleware;
use App\Services\User\UserBranchService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
public function share(
    Request $request
): array {
    $user = $request->user();

    $currentBranch = null;

    if ($user) {
        $userBranchService =
            app(UserBranchService::class);

        $currentBranch =
            $userBranchService
                ->getCurrentBranch($user);
    }

    return [

        ...parent::share($request),

        'auth' => [

            'user' => $user
                ? [

                    'id' =>
                        $user->id,

                    'name' =>
                        $user->name,

                    'email' =>
                        $user->email,

                ]
                : null,

            'current_branch' =>
                $currentBranch
                    ? [
                        'id' =>
                            $currentBranch->id,

                        'company_id' =>
                            $currentBranch->company_id,

                        'code' =>
                            $currentBranch->code,

                        'name' =>
                            $currentBranch->name,
                    ]
                    : null,

            'branches' => $user
                ? $user->branches()
                    ->where(
                        'branches.is_active',
                        true
                    )
                    ->orderBy('name')
                    ->get([
                        'branches.id',
                        'branches.company_id',
                        'branches.code',
                        'branches.name',
                    ])
                : [],

        ],

        'app' => [

            'name' =>
                config('app.name'),

            'version' =>
                '1.0.0',

        ],

        'flash' => [

            'success' => fn () =>
                $request
                    ->session()
                    ->get('success'),

            'warning' => fn () =>
                $request
                    ->session()
                    ->get('warning'),

            'error' => fn () =>
                $request
                    ->session()
                    ->get('error'),

            'settlement_success' => fn () =>
                $request
                    ->session()
                    ->get(
                        'settlement_success'
                    ),
        ],
    ];
}
}
