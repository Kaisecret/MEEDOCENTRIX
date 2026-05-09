<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', static function (Request $request): Limit {
            $identifier = mb_strtolower(trim((string) $request->input('email', '')));
            $key = ($identifier !== '' ? $identifier : 'guest') . '|' . $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('admin-login', static function (Request $request): Limit {
            $identifier = mb_strtolower(trim((string) $request->input('email', '')));
            $key = 'admin|' . ($identifier !== '' ? $identifier : 'guest') . '|' . $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('sensitive-actions', static function (Request $request): Limit {
            $actor = (string) ($request->user()?->id ?? 'guest');
            $routeName = (string) ($request->route()?->getName() ?? 'unknown');
            $key = $actor . '|' . $routeName . '|' . $request->ip();

            return Limit::perMinute(20)->by($key);
        });

        RateLimiter::for('approval-actions', static function (Request $request): Limit {
            $actor = (string) ($request->user()?->id ?? 'guest');
            $routeName = (string) ($request->route()?->getName() ?? 'unknown');
            $key = 'approval|' . $actor . '|' . $routeName . '|' . $request->ip();

            return Limit::perMinute(12)->by($key);
        });

        RateLimiter::for('destructive-actions', static function (Request $request): Limit {
            $actor = (string) ($request->user()?->id ?? 'guest');
            $routeName = (string) ($request->route()?->getName() ?? 'unknown');
            $key = 'delete|' . $actor . '|' . $routeName . '|' . $request->ip();

            return Limit::perMinute(8)->by($key);
        });
    }
}
