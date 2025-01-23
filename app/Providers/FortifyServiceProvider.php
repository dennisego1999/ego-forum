<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\FailedPasswordResetLinkRequestResponse;
use App\Http\Responses\LoginResponse;
use App\Models\Role;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Fortify::ignoreRoutes();

        $this->app->bind(
            \Laravel\Fortify\Http\Responses\FailedPasswordResetLinkRequestResponse::class,
            FailedPasswordResetLinkRequestResponse::class
        );

        $this->app->bind(
            \Laravel\Fortify\Http\Responses\LoginResponse::class,
            LoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(static fn (Request $request) => Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'loginLinks' => function () use ($request) {
                // Check state
                $isLoginAllowed = in_array(app()->environment(), config('login-link.allowed_environments'), true);
                $isSharedWithExpose = str_contains($request->httpHost(), 'sharedwithexpose.com');
                $superAdminEmail = config('auth.super_admin.email');

                // Bail when no login links should be rendered
                if (! $isLoginAllowed || $isSharedWithExpose || ! $superAdminEmail) {
                    return [];
                }

                // Map available roles into login links
                return Role::query()->get()->mapWithKeys(function (Role $role) use ($superAdminEmail) {
                    return [$role->getEmail($superAdminEmail) => "login as $role->name"];
                });
            },
        ]));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
