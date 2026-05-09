<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use App\Models\TerminalQuickPayment;
use App\Models\TerminalRouteFare;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TerminalParkingController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderSimplePaymentsPage(
            $request,
            'terminal.transactions',
            'terminal_records',
            'Terminal Transactions',
            false
        );
    }

    public function sendPayment(Request $request): View
    {
        return $this->renderSimplePaymentsPage(
            $request,
            'terminal.send_payment',
            'send_payment',
            'Payment History',
            true
        );
    }

    public function storeSimplePayment(Request $request): RedirectResponse
    {
        $routeConfig = $this->routeFareConfig();
        $routeCodes = array_keys($routeConfig);

        $validated = $request->validate([
            'payer_name' => ['nullable', 'string', 'max:160'],
            'ticket_number' => ['required', 'digits:6', 'unique:terminal_quick_payments,ticket_number'],
            'route_code' => ['required', 'string', Rule::in($routeCodes)],
            'payment_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $selectedRoute = $routeConfig[(string) $validated['route_code']];

        TerminalQuickPayment::query()->create([
            'payer_name' => trim((string) ($validated['payer_name'] ?? '')) !== ''
                ? trim((string) $validated['payer_name'])
                : 'N/A',
            'ticket_number' => trim((string) $validated['ticket_number']),
            'vehicle_kind' => $selectedRoute['vehicle_kind'],
            'route_name' => $selectedRoute['label'],
            'route_code' => (string) $validated['route_code'],
            'total_payment' => round((float) $selectedRoute['fare'], 2),
            'payment_date' => isset($validated['payment_date']) && trim((string) $validated['payment_date']) !== ''
                ? Carbon::parse((string) $validated['payment_date'])
                : now(),
            'remarks' => $validated['remarks'] ?: null,
            'recorded_by_user_id' => Auth::id(),
            'is_paid' => false,
            'paid_at' => null,
            'paid_by_user_id' => null,
        ]);

        return redirect()->back()->with('status', 'Payment saved successfully.');
    }

    public function updateSimplePayment(Request $request, TerminalQuickPayment $quickPayment): RedirectResponse
    {
        if ($quickPayment->is_paid) {
            return redirect()->back()->with('error', 'Paid records are read-only and can only be viewed in Payment History.');
        }

        $routeConfig = $this->routeFareConfig();
        $routeCodes = array_keys($routeConfig);

        $validated = $request->validate([
            'payer_name' => ['nullable', 'string', 'max:160'],
            'ticket_number' => [
                'required',
                'digits:6',
                Rule::unique('terminal_quick_payments', 'ticket_number')->ignore($quickPayment->id),
            ],
            'route_code' => ['required', 'string', Rule::in($routeCodes)],
            'payment_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $selectedRoute = $routeConfig[(string) $validated['route_code']];

        $quickPayment->update([
            'payer_name' => trim((string) ($validated['payer_name'] ?? '')) !== ''
                ? trim((string) $validated['payer_name'])
                : 'N/A',
            'ticket_number' => trim((string) $validated['ticket_number']),
            'vehicle_kind' => $selectedRoute['vehicle_kind'],
            'route_name' => $selectedRoute['label'],
            'route_code' => (string) $validated['route_code'],
            'total_payment' => round((float) $selectedRoute['fare'], 2),
            'payment_date' => isset($validated['payment_date']) && trim((string) $validated['payment_date']) !== ''
                ? Carbon::parse((string) $validated['payment_date'])
                : now(),
            'remarks' => $validated['remarks'] ?: null,
            'recorded_by_user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('status', 'Payment updated successfully.');
    }

    public function destroySimplePayment(TerminalQuickPayment $quickPayment): RedirectResponse
    {
        if ($quickPayment->is_paid) {
            return redirect()->back()->with('error', 'Paid records are read-only and cannot be deleted from Payment History.');
        }

        $quickPayment->delete();

        return redirect()->back()->with('status', 'Payment deleted successfully.');
    }

    public function markSimplePaymentPaid(TerminalQuickPayment $quickPayment): RedirectResponse
    {
        if ($quickPayment->is_paid) {
            return redirect()->route('terminal.send_payment')->with('status', 'Payment is already marked as paid.');
        }

        $quickPayment->update([
            'is_paid' => true,
            'paid_at' => now(),
            'paid_by_user_id' => Auth::id(),
        ]);

        return redirect()
            ->route('terminal.send_payment')
            ->with('status', 'Payment marked as paid and moved to Payment History.');
    }

    private function renderSimplePaymentsPage(
        Request $request,
        string $view,
        string $serverRenderedPage,
        string $pageTitle,
        bool $historyMode
    ): View {
        $search = trim((string) $request->query('q', ''));
        $period = strtolower(trim((string) $request->query('period', 'all')));
        if (! in_array($period, ['all', 'today', 'week', 'month', 'custom'], true)) {
            $period = 'all';
        }

        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        [$rangeStart, $rangeEnd, $dateFrom, $dateTo, $period] = $this->resolvePeriodRange($period, $dateFrom, $dateTo);

        $dateColumn = $historyMode ? 'paid_at' : 'payment_date';

        $payments = TerminalQuickPayment::query()
            ->with(['recordedBy:id,name', 'paidBy:id,name'])
            ->where('is_paid', $historyMode)
            ->whereNotNull('ticket_number')
            ->where('ticket_number', '<>', '')
            ->whereNotNull('route_code')
            ->where('route_code', '<>', '')
            ->when($search !== '', static function ($query) use ($search): void {
                $like = '%' . $search . '%';
                $query->where(function ($nested) use ($like): void {
                    $nested->where('payer_name', 'like', $like)
                        ->orWhere('ticket_number', 'like', $like)
                        ->orWhere('vehicle_kind', 'like', $like)
                        ->orWhere('route_name', 'like', $like)
                        ->orWhere('remarks', 'like', $like);
                });
            })
            ->when($rangeStart !== null && $rangeEnd !== null, static function ($query) use ($rangeStart, $rangeEnd, $dateColumn): void {
                $query->whereBetween($dateColumn, [$rangeStart, $rangeEnd]);
            })
            ->orderByDesc($dateColumn)
            ->orderByDesc('id')
            ->paginate(10)
            ->onEachSide(1)
            ->withQueryString();

        $routeFareConfig = $this->routeFareConfig();

        return view($view, [
            'payments' => $payments,
            'search' => $search,
            'serverRenderedPage' => $serverRenderedPage,
            'pageTitle' => $pageTitle,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'isHistoryMode' => $historyMode,
            'routeFareConfig' => $routeFareConfig,
            'routeGroups' => $this->routeFareGroups($routeFareConfig),
        ]);
    }

    /**
     * @return array<string, array{label: string, vehicle_kind: string, fare: float}>
     */
    private function routeFareConfig(): array
    {
        if (! Schema::hasTable('terminal_route_fares')) {
            return $this->fallbackRouteFareConfig();
        }

        try {
            $routes = TerminalRouteFare::query()
                ->where('is_active', true)
                ->orderBy('vehicle_kind')
                ->orderBy('fare_amount')
                ->orderBy('sort_order')
                ->orderBy('route_name')
                ->get(['code', 'vehicle_kind', 'route_name', 'fare_amount']);
        } catch (QueryException $exception) {
            if ($this->isMissingTableException($exception, 'terminal_route_fares')) {
                return $this->fallbackRouteFareConfig();
            }

            throw $exception;
        }

        if ($routes->isEmpty()) {
            return $this->fallbackRouteFareConfig();
        }

        return $routes->mapWithKeys(static function (TerminalRouteFare $route): array {
            return [
                (string) $route->code => [
                    'label' => (string) $route->route_name,
                    'vehicle_kind' => (string) $route->vehicle_kind,
                    'fare' => round((float) $route->fare_amount, 2),
                ],
            ];
        })->all();
    }

    /**
     * @return array<string, array{label: string, vehicle_kind: string, fare: float}>
     */
    private function fallbackRouteFareConfig(): array
    {
        return [
            'jeep_bugasong' => ['label' => 'Bugasong', 'vehicle_kind' => 'Jeep', 'fare' => 20.00],
            'jeep_lindero' => ['label' => 'Lindero', 'vehicle_kind' => 'Jeep', 'fare' => 20.00],
            'jeep_guinsangan' => ['label' => 'Guinsang-an', 'vehicle_kind' => 'Jeep', 'fare' => 20.00],
            'jeep_patnongon' => ['label' => 'Patnongon', 'vehicle_kind' => 'Jeep', 'fare' => 20.00],
            'jeep_sibalom' => ['label' => 'Sibalom', 'vehicle_kind' => 'Jeep', 'fare' => 20.00],
            'jeep_bugo' => ['label' => 'Bugo', 'vehicle_kind' => 'Jeep', 'fare' => 20.00],
            'jeep_san_remegio' => ['label' => 'San Remegio', 'vehicle_kind' => 'Jeep', 'fare' => 20.00],
            'jeep_dao' => ['label' => 'Dao', 'vehicle_kind' => 'Jeep', 'fare' => 35.00],
            'jeep_aniniy' => ['label' => 'Anini-y', 'vehicle_kind' => 'Jeep', 'fare' => 35.00],
            'jeep_valderrama' => ['label' => 'Valderrama', 'vehicle_kind' => 'Jeep', 'fare' => 35.00],
            'bus_ceres_iloilo' => ['label' => 'Ceres - Iloilo', 'vehicle_kind' => 'Bus', 'fare' => 60.00],
            'bus_roro_alps' => ['label' => 'Roro - ALPS', 'vehicle_kind' => 'Bus', 'fare' => 100.00],
            'bus_roro_ceres' => ['label' => 'Roro - Ceres', 'vehicle_kind' => 'Bus', 'fare' => 100.00],
        ];
    }

    private function isMissingTableException(QueryException $exception, string $tableName): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $driverCode = (string) ($exception->errorInfo[1] ?? '');
        $message = strtolower($exception->getMessage());

        return $sqlState === '42S02'
            || $driverCode === '1146'
            || str_contains($message, strtolower($tableName) . "' doesn't exist")
            || str_contains($message, strtolower($tableName) . '` doesn\'t exist')
            || str_contains($message, 'base table or view not found');
    }

    /**
     * @param array<string, array{label: string, vehicle_kind: string, fare: float}> $routeConfig
     * @return array<string, array<int, string>>
     */
    private function routeFareGroups(array $routeConfig): array
    {
        $groups = [];

        foreach ($routeConfig as $routeCode => $route) {
            $groupLabel = trim((string) $route['vehicle_kind']) . ' - PHP ' . number_format((float) $route['fare'], 2);
            if (! array_key_exists($groupLabel, $groups)) {
                $groups[$groupLabel] = [];
            }
            $groups[$groupLabel][] = (string) $routeCode;
        }

        return $groups;
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: string, 3: string, 4: string}
     */
    private function resolvePeriodRange(string $period, string $dateFrom, string $dateTo): array
    {
        if ($period === 'today') {
            return [
                now()->copy()->startOfDay(),
                now()->copy()->endOfDay(),
                now()->toDateString(),
                now()->toDateString(),
                'today',
            ];
        }

        if ($period === 'week') {
            return [
                now()->copy()->startOfWeek()->startOfDay(),
                now()->copy()->endOfWeek()->endOfDay(),
                now()->copy()->startOfWeek()->toDateString(),
                now()->copy()->endOfWeek()->toDateString(),
                'week',
            ];
        }

        if ($period === 'month') {
            return [
                now()->copy()->startOfMonth()->startOfDay(),
                now()->copy()->endOfMonth()->endOfDay(),
                now()->copy()->startOfMonth()->toDateString(),
                now()->copy()->endOfMonth()->toDateString(),
                'month',
            ];
        }

        if ($period === 'custom') {
            $from = $this->parseDate($dateFrom);
            $to = $this->parseDate($dateTo);

            if ($from !== null && $to !== null) {
                $start = $from->lte($to) ? $from->copy()->startOfDay() : $to->copy()->startOfDay();
                $end = $from->lte($to) ? $to->copy()->endOfDay() : $from->copy()->endOfDay();

                return [
                    $start,
                    $end,
                    $start->toDateString(),
                    $end->toDateString(),
                    'custom',
                ];
            }

            return [null, null, $dateFrom, $dateTo, 'custom'];
        }

        return [null, null, '', '', 'all'];
    }

    private function parseDate(string $value): ?Carbon
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $trimmed);
        } catch (\Throwable) {
            return null;
        }
    }
}
