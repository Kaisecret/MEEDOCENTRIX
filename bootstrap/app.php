<?php

use App\Http\Middleware\EnsureAreaAccess;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ScopeSessionByArea;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Must run before session/auth middleware so the proper scoped
        // session cookie is selected for each request.
        $middleware->prepend([
            ScopeSessionByArea::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);

        $middleware->alias([
            'auth' => Authenticate::class,
            'admin' => EnsureAdmin::class,
            'area' => EnsureAreaAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
