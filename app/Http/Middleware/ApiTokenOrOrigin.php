<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public GET endpoints: allow Bearer token (admin) OR origin-restricted access (website).
 */
class ApiTokenOrOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            return app(ApiTokenAuth::class)->handle($request, $next);
        }

        return app(RestrictApiByOrigin::class)->handle($request, $next);
    }
}
