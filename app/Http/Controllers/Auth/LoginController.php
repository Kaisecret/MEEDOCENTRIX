<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const GENERIC_LOGIN_ERROR = 'Incorrect email or password. Please try again.';

    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            // Force a clean login scope so this page is always usable
            // for signing in a different account in another tab.
            $this->clearAuthenticatedSession($request);
        }

        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $identifier = mb_strtolower(trim((string) $validated['email']));

        /** @var User|null $user */
        $user = User::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(email) = ?', [$identifier])
            ->first();

        if (! $user || ! Hash::check((string) $validated['password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => self::GENERIC_LOGIN_ERROR,
            ]);
        }

        if ($user->isAdmin()) {
            throw ValidationException::withMessages([
                'email' => self::GENERIC_LOGIN_ERROR,
            ]);
        }

        $scope = $this->scopeForUser($user);
        $tabToken = $scope === 'collector' ? Str::random(10) : null;

        $this->switchSessionScope($request, $scope, $tabToken);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($tabToken !== null) {
            return redirect()->route($user->dashboardRouteName(), ['s' => $tabToken]);
        }

        return redirect()->route($user->dashboardRouteName());
    }

    public function showAdminLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $authenticatedUser */
            $authenticatedUser = Auth::user();

            if (! $authenticatedUser->isAdmin()) {
                // Clear stale non-admin session state on admin login scope.
                $this->clearAuthenticatedSession($request);
            }
        }

        return view('admin-login');
    }

    public function adminLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $identifier = mb_strtolower(trim((string) $validated['email']));

        $adminUser = User::query()
            ->where('is_active', true)
            ->where(function ($query) use ($identifier): void {
                $query->whereRaw('LOWER(email) = ?', [$identifier])
                    ->orWhereRaw('LOWER(username) = ?', [$identifier]);
            })
            ->where(function ($query): void {
                $query->whereRaw('LOWER(role) = ?', ['admin'])
                    ->orWhereRaw('LOWER(role) = ?', ['administrator']);
            })
            ->first();

        if (! $adminUser || ! Hash::check((string) $validated['password'], (string) $adminUser->password)) {
            throw ValidationException::withMessages([
                'email' => 'Incorrect admin email/username or password. Please try again.',
            ]);
        }

        $this->switchSessionScope($request, 'admin');
        Auth::login($adminUser, $request->boolean('remember'));
        $request->session()->regenerate();

        // Always land on admin dashboard to avoid stale intended redirects.
        $request->session()->forget('url.intended');

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function switchSessionScope(Request $request, string $scope, ?string $tabToken = null): void
    {
        $cookieName = $this->scopedCookieName($scope, $tabToken);

        $session = $request->session();
        $session->setName($cookieName);
        config(['session.cookie' => $cookieName]);
    }

    private function scopedCookieName(string $scope, ?string $tabToken = null): string
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

    private function scopeForUser(User $user): string
    {
        $roleKey = $user->uiRoleKey();

        return $roleKey === 'administrator' ? 'admin' : $roleKey;
    }

    private function clearAuthenticatedSession(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
