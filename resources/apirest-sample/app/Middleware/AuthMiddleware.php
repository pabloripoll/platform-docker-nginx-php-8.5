<?php

namespace App\Middleware;

use Core\Request;

class AuthMiddleware
{
    public function handle(Request $request, $next)
    {
        // do auth checks...
        // if ok:
        return $next($request);
        // otherwise:
        // http_response_code(401); return 'Unauthorized';
    }
}
