<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->is('api/*')) {
            return $response;
        }

        if (! str_contains((string) $response->headers->get('Content-Type', ''), 'json')) {
            return $response;
        }

        if (! function_exists('gzencode')) {
            return $response;
        }

        $accept = (string) $request->header('Accept-Encoding', '');
        if (! str_contains($accept, 'gzip')) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || strlen($content) < 1024) {
            return $response;
        }

        $compressed = gzencode($content, 6);
        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip', true);
        $response->headers->remove('Content-Length');

        return $response;
    }
}
