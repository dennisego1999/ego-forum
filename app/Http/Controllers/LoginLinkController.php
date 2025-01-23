<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Spatie\LoginLink\Http\Requests\LoginLinkRequest;

class LoginLinkController extends \Spatie\LoginLink\Http\Controllers\LoginLinkController
{
    /** @noinspection PhpUnhandledExceptionInspection */
    public function __invoke(LoginLinkRequest $request)
    {
        $this->ensureAllowedEnvironment();

        $authenticatable = $this->getAuthenticatable($request);

        $this->performLogin($request->guard, $authenticatable);

        $redirectUrl = $this->getRedirectUrl($request);

        return Inertia::location($redirectUrl);
    }
}
