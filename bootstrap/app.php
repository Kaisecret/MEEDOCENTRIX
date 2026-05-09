<?php

use App\Http\Middleware\EnsureAreaAccess;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ScopeSessionByArea;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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
        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request) {
            $retryAfter = (int) ($exception->getHeaders()['Retry-After'] ?? 60);
            if ($retryAfter < 1) {
                $retryAfter = 60;
            }

            $title = $request->is('login') || $request->is('admin-login')
                ? 'Too Many Login Attempts'
                : 'Too Many Requests';

            $message = $request->is('login') || $request->is('admin-login')
                ? 'Login is temporarily limited for security. Please try again after ' . $retryAfter . ' second(s).'
                : 'This action is temporarily limited. Please try again after ' . $retryAfter . ' second(s).';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                    'retry_after' => $retryAfter,
                ], 429, $exception->getHeaders());
            }

            return redirect()
                ->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('rate_limit_warning', [
                    'title' => $title,
                    'message' => $message,
                    'retry_after' => $retryAfter,
                ]);
        });
    })->create();
