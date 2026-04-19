<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeSessionByArea
{
    /**
     * @var array<int, string>
     */
    private const KNOWN_SCOPES = [
        'shared',
        'login',
        'admin',
        'fishport',
        'market',
        'cemetery',
        'terminal',
        'atrium',
        'collector',
        'cashier',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $scope = $this->resolveScope($request);

        $tabToken = null;
        if ($scope === 'collector') {
            $tabToken = $this->resolveTabToken($request);

            // If this is a collector page load (GET to an HTML page) without a token,
            // but the referer has one, bounce to the same URL with ?s= appended so
            // the user keeps their tab's session instead of falling into the no-token
            // cookie (which has no auth state → false "logout").
            if ($tabToken === null && $request->isMethod('GET') && ! $request->ajax()) {
                $refererToken = $this->tokenFromRefererQuery($request->headers->get('referer'));
                if ($refererToken !== null) {
                    $target = $request->url() . '?' . http_build_query(
                        array_merge($request->query(), ['s' => $refererToken])
                    );

                    return redirect()->to($target);
                }
            }
        }

        $cookieName = $this->buildScopedCookieName($scope, $tabToken);
        config(['session.cookie' => $cookieName]);

        if ($request->hasSession()) {
            $request->session()->setName($cookieName);
        }

        return $next($request);
    }

    private function tokenFromRefererQuery(?string $referer): ?string
    {
        if (! \is_string($referer) || trim($referer) === '') {
            return null;
        }

        $query = (string) parse_url($referer, PHP_URL_QUERY);
        if ($query === '') {
            return null;
        }

        parse_str($query, $parsed);
        if (! isset($parsed['s'])) {
            return null;
        }

        $clean = preg_replace('/[^a-zA-Z0-9]/', '', (string) $parsed['s']);
        if (! \is_string($clean) || $clean === '') {
            return null;
        }

        return substr($clean, 0, 16);
    }

    private function resolveTabToken(Request $request): ?string
    {
        $candidates = [
            (string) $request->query('s', ''),
            (string) $request->input('s', ''),
        ];

        $referer = $request->headers->get('referer');
        if (is_string($referer) && trim($referer) !== '') {
            $query = (string) parse_url($referer, PHP_URL_QUERY);
            if ($query !== '') {
                parse_str($query, $parsed);
                if (isset($parsed['s'])) {
                    $candidates[] = (string) $parsed['s'];
                }
            }
        }

        foreach ($candidates as $value) {
            $clean = preg_replace('/[^a-zA-Z0-9]/', '', $value);
            if (\is_string($clean) && $clean !== '') {
                return substr($clean, 0, 16);
            }
        }

        return null;
    }

    private function resolveScope(Request $request): string
    {
        // Keep login sessions isolated from area sessions.
        if ($request->is('login')) {
            return 'login';
        }

        // Ensure every admin URL uses the same admin-scoped session cookie.
        if ($request->is('admin-login') || $request->is('admin') || $request->is('admin/*') || $request->routeIs('admin.*')) {
            return 'admin';
        }

        $firstSegment = strtolower((string) $request->segment(1));
        if ($this->isKnownScope($firstSegment)) {
            return $firstSegment;
        }

        // For shared endpoints like /logout, prefer URL query and referer path.
        // These are reliably available before CSRF validation.
        $queryScope = strtolower((string) $request->query('session_scope', ''));
        if ($this->isKnownScope($queryScope)) {
            return $queryScope;
        }

        $refererScope = $this->scopeFromReferer($request->headers->get('referer'));
        if ($refererScope !== null) {
            return $refererScope;
        }

        $explicitScope = strtolower((string) $request->input('session_scope', ''));
        if ($this->isKnownScope($explicitScope)) {
            return $explicitScope;
        }

        return 'shared';
    }

    private function scopeFromReferer(?string $referer): ?string
    {
        if (! is_string($referer) || trim($referer) === '') {
            return null;
        }

        $path = (string) parse_url($referer, PHP_URL_PATH);
        $path = trim($path, '/');
        if ($path === '') {
            return null;
        }

        $firstSegment = strtolower((string) explode('/', $path)[0]);
        if (! $this->isKnownScope($firstSegment)) {
            return null;
        }

        return $firstSegment;
    }

    private function buildScopedCookieName(string $scope, ?string $tabToken = null): string
    {
        $baseName = (string) config('session.cookie_base', config('session.cookie', 'laravel-session'));
        $separator = (string) config('session.cookie_scope_separator', '__');

        $normalizedScope = preg_replace('/[^a-z0-9_-]/', '', strtolower($scope)) ?: 'shared';
        $cookie = $baseName . $separator . $normalizedScope;

        if (\is_string($tabToken) && $tabToken !== '') {
            $cookie .= '_' . $tabToken;
        }

        return $cookie;
    }

    private function isKnownScope(string $value): bool
    {
        return \in_array($value, self::KNOWN_SCOPES, true);
    }
}
