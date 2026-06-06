<?php

use App\Http\Middleware\ApiTimeout;
use App\Http\Middleware\CompressJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('api', [
            ApiTimeout::class,
            CompressJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $message = $e->getMessage();
            if (str_contains($message, 'Maximum execution time')
                || str_contains($message, 'execution time')
                || str_contains($message, 'timed out')) {
                return response()->json([
                    'error' => 'Yêu cầu xử lý quá thời gian cho phép. Vui lòng thử lại.',
                    'code' => 'timeout',
                ], Response::HTTP_GATEWAY_TIMEOUT);
            }

            return null;
        });
    })->create();
