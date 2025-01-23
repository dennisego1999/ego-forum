<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class HandleInertiaGuestRequests extends HandleInertiaRequests
{
    private array $onlyRoutes = [
        'login',
        'password.*',
        'two-factor.login',
        'safe-share',
        'safe-share.login',
    ];

    public function share(Request $request): array
    {
        // Abort logged-in or on excluded endpoints
        if (Auth::check() || $this->isExcluded($request)) {
            return [];
        }

        if (App::isLocal()) {
            $this->onlyRoutes[] = 'loginLinkLogin';
        }

        // Restrict routes in Ziggy
        config(['ziggy.only' => $this->onlyRoutes]);

        // Get the shared data
        $data = [
            ...parent::share($request),
            'flash' => [
                'uuid' => (string) Str::uuid(),
                'success' => Session::get('success'),
                'error' => Session::get('error'),
            ],
            // General setup
            'environment' => fn () => app()->environment(),
            'locales' => fn () => $this->getLocales(),
        ];

        // Only on the initial load
        if (! $request->inertia()) {
            $data['app_name'] = config('app.name');
            $data['translations'] = $this->getTranslations();
        }

        return $data;
    }
}
