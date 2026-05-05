<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureAreaAccess
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($user->uiRoleKey() !== $area) {
            return redirect()
                ->route($user->dashboardRouteName())
                ->with('error', 'You do not have permission to access that section.');
        }

        $routeName = (string) ($request->route()?->getName() ?? '');
        $requiredPermission = $this->resolveRequiredPermission($area, $routeName);

        if ($requiredPermission === null || $user->hasPermission($requiredPermission)) {
            return $next($request);
        }

        $fallbackRoute = $this->resolveFallbackRouteName($user, $area, $routeName);
        if ($fallbackRoute !== null) {
            return redirect()
                ->route($fallbackRoute)
                ->with('error', 'You do not have permission to access that page.');
        }

        return redirect()
            ->route('home')
            ->with('error', 'You do not have permission to access that page.');
    }

    private function resolveRequiredPermission(string $area, string $routeName): ?string
    {
        $prefix = $area . '.';
        if (! str_starts_with($routeName, $prefix)) {
            return null;
        }

        $action = substr($routeName, strlen($prefix));
        if ($action === false) {
            return null;
        }

        if (str_starts_with($action, 'dashboard')) {
            return $area . '.dashboard.view';
        }

        if (str_starts_with($action, 'profile')) {
            return null;
        }

        if (str_starts_with($action, 'reports')) {
            if ($area === 'terminal') {
                if (str_ends_with($action, '.pdf') || str_ends_with($action, '.csv')) {
                    return 'terminal.payments.collect';
                }

                return 'terminal.records.view';
            }

            if (str_ends_with($action, '.pdf') || str_ends_with($action, '.csv')) {
                return $area . '.reports.export';
            }

            return $area . '.reports.view';
        }

        return match ($area) {
            'fishport' => $this->resolveFishportPermission($action),
            'market' => $this->resolveMarketPermission($action),
            'cemetery' => $this->resolveCemeteryPermission($action),
            'terminal' => $this->resolveTerminalPermission($action),
            'atrium' => $this->resolveAtriumPermission($action),
            'collector' => $this->resolveCollectorPermission($action),
            'cashier' => $this->resolveCashierPermission($action),
            default => null,
        };
    }

    private function resolveFallbackRouteName($user, string $area, string $currentRouteName): ?string
    {
        $candidates = match ($area) {
            'fishport' => [
                ['fishport.records', 'fishport.records.view'],
                ['fishport.send_payment', 'fishport.payments.send'],
                ['fishport.reports', 'fishport.reports.view'],
                ['fishport.profile', null],
            ],
            'market' => [
                ['market.stalls', 'market.stalls.view'],
                ['market.records', 'market.stalls.view'],
                ['market.send_payment', 'market.payments.send'],
                ['market.reports', 'market.reports.view'],
                ['market.profile', null],
            ],
            'cemetery' => [
                ['cemetery.records', 'cemetery.records.view'],
                ['cemetery.transactions', 'cemetery.transactions.view'],
                ['cemetery.payments', 'cemetery.payments.collect'],
                ['cemetery.reports', 'cemetery.reports.view'],
                ['cemetery.profile', null],
            ],
            'terminal' => [
                ['terminal.records', 'terminal.records.view'],
                ['terminal.reports', 'terminal.records.view'],
                ['terminal.send_payment', 'terminal.payments.collect'],
            ],
            'atrium' => [
                ['atrium.bookings', 'atrium.bookings.view'],
                ['atrium.payments', 'atrium.payments.collect'],
                ['atrium.reports', 'atrium.reports.view'],
                ['atrium.profile', null],
            ],
            'collector' => [
                ['collector.pending_collections', 'collector.collections.view'],
                ['collector.payments', 'collector.collections.view'],
                ['collector.reports', 'collector.reports.view'],
                ['collector.profile', null],
                ['collector.remit', null],
            ],
            'cashier' => [
                ['cashier.collections', 'cashier.collections.view'],
                ['cashier.remittance', 'cashier.remittance.view'],
                ['cashier.summary', 'cashier.summary.view'],
            ],
            default => [],
        };

        foreach ($candidates as [$routeName, $permission]) {
            if (! Route::has($routeName) || $routeName === $currentRouteName) {
                continue;
            }

            if ($permission === null || $user->hasPermission($permission)) {
                return $routeName;
            }
        }

        $dashboardRoute = $user->dashboardRouteName();
        if ($dashboardRoute !== $currentRouteName && Route::has($dashboardRoute)) {
            $dashboardPermission = $area . '.dashboard.view';
            if ($user->hasPermission($dashboardPermission)) {
                return $dashboardRoute;
            }
        }

        return null;
    }

    private function resolveFishportPermission(string $action): ?string
    {
        if (str_starts_with($action, 'records')) {
            if (str_ends_with($action, '.store')) return 'fishport.records.create';
            if (str_ends_with($action, '.destroy')) return 'fishport.records.delete';
            if (str_contains($action, '.update') || str_contains($action, 'mark_paid') || str_contains($action, 'cancel_payment')) {
                return 'fishport.records.update';
            }

            return 'fishport.records.view';
        }

        if (str_starts_with($action, 'vessel_logs') || str_starts_with($action, 'vessel_registry')) {
            if (str_contains($action, '.store')) return 'fishport.records.create';
            if (str_contains($action, '.destroy')) return 'fishport.records.delete';
            if (str_contains($action, '.update') || str_contains($action, 'toggle_active')) return 'fishport.records.update';

            return 'fishport.records.view';
        }

        if (str_starts_with($action, 'send_payment')) {
            if (str_contains($action, '.approve') || str_contains($action, '.reject')) return 'fishport.payments.approve';
            return 'fishport.payments.send';
        }

        return null;
    }

    private function resolveMarketPermission(string $action): ?string
    {
        if (str_starts_with($action, 'stalls')) {
            if (str_contains($action, '.store')) return 'market.stalls.create';
            if (str_contains($action, '.destroy')) return 'market.stalls.delete';
            if (str_contains($action, '.update') || str_contains($action, '.rates') || str_contains($action, '.locations')) return 'market.stalls.update';
            return 'market.stalls.view';
        }

        if (str_starts_with($action, 'vendors') || str_starts_with($action, 'records')) {
            return 'market.stalls.view';
        }

        if (str_starts_with($action, 'send_payment')) {
            if (str_contains($action, '.approve') || str_contains($action, '.reject')) return 'market.payments.approve';
            return 'market.payments.send';
        }

        return null;
    }

    private function resolveCemeteryPermission(string $action): ?string
    {
        if (str_starts_with($action, 'records') || str_starts_with($action, 'services')) {
            if (str_contains($action, '.store')) return 'cemetery.records.create';
            if (str_contains($action, '.destroy')) return 'cemetery.records.delete';
            if (str_contains($action, '.update')) return 'cemetery.records.update';
            return 'cemetery.records.view';
        }

        if (str_starts_with($action, 'transactions')) {
            if (str_contains($action, '.store')) return 'cemetery.transactions.create';
            return 'cemetery.transactions.view';
        }

        if (str_starts_with($action, 'payments')) {
            return 'cemetery.payments.collect';
        }

        return null;
    }

    private function resolveTerminalPermission(string $action): ?string
    {
        if (str_starts_with($action, 'records')) {
            return 'terminal.records.view';
        }

        if (str_starts_with($action, 'send_payment') || str_starts_with($action, 'simple_payments')) {
            return 'terminal.payments.collect';
        }

        return null;
    }

    private function resolveAtriumPermission(string $action): ?string
    {
        if (str_starts_with($action, 'bookings') || str_starts_with($action, 'records')) {
            if (str_contains($action, '.store') || str_ends_with($action, '.create')) return 'atrium.bookings.create';
            if (str_contains($action, '.destroy')) return 'atrium.bookings.delete';
            if (str_contains($action, '.update') || str_contains($action, '.edit') || str_contains($action, '.cancel') || str_contains($action, '.complete')) {
                return 'atrium.bookings.update';
            }

            return 'atrium.bookings.view';
        }

        if (str_starts_with($action, 'payments')) {
            return 'atrium.payments.collect';
        }

        return null;
    }

    private function resolveCollectorPermission(string $action): ?string
    {
        if (str_starts_with($action, 'pending_collections') || str_starts_with($action, 'payments')) {
            if (str_contains($action, '.collect')) return 'collector.collections.collect';
            return 'collector.collections.view';
        }

        if (str_starts_with($action, 'reports')) {
            return 'collector.reports.view';
        }

        return null;
    }

    private function resolveCashierPermission(string $action): ?string
    {
        if (str_starts_with($action, 'collections')) return 'cashier.collections.view';
        if (str_starts_with($action, 'summary')) return 'cashier.summary.view';
        if (str_starts_with($action, 'remittance')) return 'cashier.remittance.view';

        return null;
    }
}
