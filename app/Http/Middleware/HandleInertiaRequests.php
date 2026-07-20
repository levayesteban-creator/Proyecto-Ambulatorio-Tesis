<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->load('role'),
            ],
        ];
    }

    public function handle(Request $request, \Closure $next): Response
    {
        if ($user = $request->user()) {
            if ($user->must_change_password && !$request->routeIs('password.force-change*')) {
                return redirect()->route('password.force-change');
            }
        }

        return parent::handle($request, $next);
    }
}
