<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocaleFromHeader::class);
        $middleware->appendToGroup('api', \App\Http\Middleware\SetLocaleFromHeader::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $isApi = fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        // 401
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        });

        // 403 — prepareException converts AuthorizationException → AccessDeniedHttpException
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            return redirect('/')->with('error', 'You do not have permission to perform this action.');
        });

        // 404 — covers ModelNotFoundException (converted by prepareException) and missing routes
        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Not found.'], 404);
            }
        });

        // 405
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Method not allowed.'], 405);
            }
        });

        // 422 — validation failures
        $exceptions->render(function (ValidationException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // 429 — rate limiting
        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Too many requests.'], 429);
            }
        });

        // 500 — unhandled exceptions; suppress stack trace on API in production
        $exceptions->render(function (\Throwable $e, Request $request) use ($isApi) {
            if ($isApi($request) && !config('app.debug')) {
                return response()->json(['message' => 'Server error.'], 500);
            }
        });

    })->create();
