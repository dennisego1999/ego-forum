<?php

namespace App\Filament\Pages;

use Illuminate\Support\Facades\Auth;

class HealthCheckResults extends \ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults
{
    public function mount(): void
    {
        abort_unless(static::shouldRegisterNavigation(), 403);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isSuperAdmin() ?: false;
    }

    public function getHeading(): string
    {
        return trans('admin.health_check_results.model');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('admin.navigation_groups.settings');
    }
}
