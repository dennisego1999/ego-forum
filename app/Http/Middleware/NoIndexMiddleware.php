<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class NoIndexMiddleware
{
    public function handle(Request $request, Closure $next, ?string $except = null): Response
    {
        // Abort on an environment being excluded when passed
        if ($except && App::environment($except)) {
            return $next($request);
        }

        // Disable indexing by crawlers
        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
