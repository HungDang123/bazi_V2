<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/la-so/export-pdf-*')) {
            $seconds = 300;
        } else {
            $seconds = max(30, (int) config('api.max_execution_seconds', 120));
        }

        set_time_limit($seconds);
        ini_set('max_execution_time', (string) $seconds);

        return $next($request);
    }
}
