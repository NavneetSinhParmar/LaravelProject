<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictApiByOrigin
{
    /**
     * Handle an incoming request.
     * Allows only GET (and OPTIONS) requests coming from configured origins.
     */
    public function handle(Request $request, Closure $next)
    {
        $allowed = array_filter(array_map('trim', explode(',', env('API_ALLOWED_ORIGINS', ''))));

        $originHeader = $request->headers->get('origin') ?: $request->headers->get('referer');
        $origin = null;
        if ($originHeader) {
            $parts = parse_url($originHeader);
            if ($parts && isset($parts['scheme']) && isset($parts['host'])) {
                $origin = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
            } else {
                $origin = rtrim($originHeader, '/');
            }
        }

        if (empty($allowed)) {
            return response('API allowed origins not configured', 403);
        }

        // If origin is missing (non-browser clients) block access to enforce domain-only access
        if (!$origin) {
            return response('Forbidden origin', 403);
        }

        if (!in_array('*', $allowed) && !in_array($origin, $allowed, true)) {
            return response('Forbidden origin', 403);
        }

        if ($request->getMethod() !== 'GET' && $request->getMethod() !== 'OPTIONS') {
            return response('Method not allowed', 405);
        }

        // Handle preflight OPTIONS
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 204);
        } else {
            $response = $next($request);
        }

        // Add CORS headers for allowed origin
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', in_array('*', $allowed, true) ? '*' : $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        return $response instanceof Response ? $response : response($response);
    }
}
