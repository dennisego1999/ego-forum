<?php

namespace App\Http\Responses;

use Inertia\Inertia;

class LoginResponse extends \Laravel\Fortify\Http\Responses\LoginResponse
{
    /** {@inheritDoc} */
    public function toResponse($request)
    {
        if ($request->inertia() && $intended = session()->pull('url.intended')) {
            return Inertia::location($intended);
        }

        return parent::toResponse($request);
    }
}
