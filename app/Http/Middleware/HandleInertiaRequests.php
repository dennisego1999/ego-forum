<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected array $excludedControllers = [
        // BroadcastController::class,
    ];

    protected array $excludedMiddleware = [
        'telescope',
    ];

    protected array $excludedRoutes = [
        'filament.*',
        'livewire.*',
        'filament-impersonate',
        'sanctum.*',
        'pulse',
        'telescope',
        'telescope.*',
    ];

    private array $hiddenZiggyRoutes = [
        'debugbar.*',
        'pretty-routes.*',
        'ignition.*',
        'telescope*',
        'horizon.*',
        'pulse',
        'livewire.*',
        'filament.*',
        'filament-*',
    ];

    public function share(Request $request): array
    {
        // Abort logged-out or on excluded endpoints
        if (Auth::guest() || $this->isExcluded($request)) {
            return parent::share($request);
        }

        // Filter routes in Ziggy
        config(['ziggy.except' => $this->hiddenZiggyRoutes]);

        // Get the shared data
        $data = [
            ...parent::share($request),
            'flash' => fn () => $this->getSessionFlashing($request),
            'auth.user' => fn () => $this->getCurrentUser($request),
            // General setup
            'locales' => fn () => $this->getLocales(),
        ];

        // Only on the initial load
        if (! $request->inertia()) {
            $data['app_name'] = config('app.name');
            $data['translations'] = $this->getTranslations();
        }

        return $data;
    }

    protected function isExcluded(Request $request): bool
    {
        // Exclude in specific routes
        if ($request->routeIs($this->excludedRoutes)) {
            return true;
        }

        // Bail without a controller
        if (blank($request->route()->controller)) {
            return false;
        }

        // Exclude in specific middlewares
        if (in_array($this->excludedMiddleware, $request->route()?->middleware(), true)) {
            return true;
        }

        // Check if we're on an excluded controller
        return in_array($request->route()->controller::class, $this->excludedControllers, true);
    }

    protected function getSessionFlashing(Request $request): array
    {
        // The default data to flash
        $flash = [
            'uuid' => (string) Str::uuid(),
            'success' => Session::get('success'),
            'error' => Session::get('error'),
        ];

        // Run additional checks when logged-in without two-factor authentication
        if (($user = $request->user()) && ! $user->hasEnabledTwoFactorAuthentication()) {
            // The user has still a valid grace period
            if ($user->is_unlocked) {
                $flash['bannerType'] = 'warning';
                $flash['bannerMessage'] = trans('auth.two_factor.time_remaining', [
                    'time' => $user->two_factor_grace_remaining,
                ]);
            }

            // The user his grace period is overdue
            if (! $user->is_unlocked) {
                $flash['bannerType'] = 'danger';
                $flash['bannerMessage'] = trans('auth.two_factor.time_overdue', [
                    'time' => $user->two_factor_grace_remaining,
                ]);
            }
        }

        return $flash;
    }

    private function getCurrentUser(Request $request): array
    {
        // The attributes to load
        $attributes = [
            'can',
            'has_two_factor_authentication',
            'two_factor_grace_remaining',
        ];

        // Return the logged-in user and load attributes
        $user = $request->user()?->append($attributes);

        return $user->toArray();
    }

    protected function getLocales(): array
    {
        return [
            'currentLocale' => config('app.locale'),
        ];
    }

    protected function getTranslations(): array
    {
        // Get the locale settings
        $locale = config('app.locale');
        $fallbackLocale = config('app.fallback_locale');

        // Get the files that should be shared with the SPA
        $files = [
            'spa',
        ];

        // Load the translations of the current locale
        foreach ($files as $file) {
            $translations[$locale][$file] = trans($file);
        }

        // Add the fallback translations when required
        if ($locale !== $fallbackLocale) {
            foreach ($files as $file) {
                $translations[$fallbackLocale][$file] = trans($file, [], $fallbackLocale);
            }
        }

        return $translations;
    }
}
