<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $isApi = fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        // 401 — AuthenticationException is NOT transformed by prepareException, so this callback runs.
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        });

        // 403 — prepareException converts AuthorizationException → AccessDeniedHttpException before
        //        renderViaCallbacks runs, so we must handle the converted type.
        //        For API requests, Laravel's default JSON rendering already returns the right response;
        //        we only need to redirect web clients.
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) use ($isApi) {
            if (!$isApi($request)) {
                return redirect('/')->with('error', 'You do not have permission to perform this action.');
            }
        });

        // 404 — NotFoundHttpException covers both direct route-not-found and the ModelNotFoundException
        //        that prepareException converts to NotFoundHttpException.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Not found.'], 404);
            }
        });

        // 405 — MethodNotAllowedHttpException is NOT transformed by prepareException.
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) use ($isApi) {
            if ($isApi($request)) {
                return response()->json(['message' => 'Method not allowed.'], 405);
            }
        });

    })->create();

