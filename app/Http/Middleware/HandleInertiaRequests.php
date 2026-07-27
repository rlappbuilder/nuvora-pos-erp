<?php

namespace App\Http\Middleware;

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
 public function share(Request $request): array
{
    return [

        ...parent::share($request),

        'auth' => [

            'user' => $request->user()

                ? [

                    'id' => $request->user()->id,

                    'name' => $request->user()->name,

                    'email' => $request->user()->email,

                ]

                : null,

        ],

        'app' => [

            'name' => config('app.name'),

            'version' => '1.0.0',

        ],

        'flash' => [

            'success' => fn () => $request->session()->get('success'),

            'warning' => fn () => $request->session()->get('warning'),

            'error' => fn () => $request->session()->get('error'),

        ],
    ];
}
}
