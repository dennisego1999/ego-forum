<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
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
        // Abort on excluded endpoints
        if ($this->isExcluded($request)) {
            return parent::share($request);
        }

        // Filter routes in Ziggy
        config(['ziggy.except' => $this->hiddenZiggyRoutes]);

        return [
            ...parent::share($request),
        ];
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
}
