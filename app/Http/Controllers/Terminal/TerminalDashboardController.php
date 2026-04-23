<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use App\Models\TerminalParkingLog;
use App\Models\TerminalParkingPayment;
use App\Models\TerminalVehicle;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TerminalDashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        $todayEntries = TerminalParkingLog::query()
            ->whereDate('entry_at', $today->toDateString())
            ->count();

        $currentlyParked = TerminalParkingLog::query()
            ->whereNull('exit_at')
            ->count();

        $readyForPayment = TerminalParkingLog::query()
            ->whereNotNull('exit_at')
            ->whereDoesntHave('payment')
            ->count();

        $todayRevenue = (float) TerminalParkingPayment::query()
            ->whereDate('payment_date', $today->toDateString())
            ->sum('paid_amount');

        $activeVehicles = TerminalVehicle::query()
            ->where('is_active', true)
            ->count();

        $recentLogs = TerminalParkingLog::query()
            ->with([
                'vehicle:id,plate_number,operator_name,terminal_vehicle_type_id',
                'vehicle.type:id,name,parking_fee_per_hour',
                'payment:id,terminal_parking_log_id,or_number,paid_amount,payment_date',
            ])
            ->orderByDesc('entry_at')
            ->limit(10)
            ->get();

        $startDay = $today->copy()->subDays(13)->startOfDay();
        $dailyEntryRows = TerminalParkingLog::query()
            ->selectRaw('DATE(entry_at) as day_key, COUNT(*) as total')
            ->whereDate('entry_at', '>=', $startDay->toDateString())
            ->groupBy('day_key')
            ->orderBy('day_key')
            ->get()
            ->keyBy('day_key');

        $dailyPaymentRows = TerminalParkingPayment::query()
            ->selectRaw('DATE(payment_date) as day_key, SUM(paid_amount) as total')
            ->whereDate('payment_date', '>=', $startDay->toDateString())
            ->groupBy('day_key')
            ->orderBy('day_key')
            ->get()
            ->keyBy('day_key');

        $dailyTrend = collect();
        $period = CarbonPeriod::create($startDay, $today->copy()->endOfDay());
        foreach ($period as $day) {
            $key = $day->toDateString();
            $dailyTrend->push([
                'label' => $day->format('M d'),
                'entries' => (int) ($dailyEntryRows->get($key)->total ?? 0),
                'revenue' => round((float) ($dailyPaymentRows->get($key)->total ?? 0), 2),
            ]);
        }

        $monthStart = $today->copy()->subMonths(5)->startOfMonth();
        $monthlyRevenueRows = TerminalParkingPayment::query()
            ->whereDate('payment_date', '>=', $monthStart->toDateString())
            ->get(['payment_date', 'paid_amount'])
            ->groupBy(static fn (TerminalParkingPayment $payment) => optional($payment->payment_date)->format('Y-m'))
            ->map(static fn ($group) => round((float) $group->sum('paid_amount'), 2));

        $monthlyRevenue = collect();
        $monthCursor = $monthStart->copy();
        $monthEnd = $today->copy()->startOfMonth();
        while ($monthCursor->lte($monthEnd)) {
            $monthKey = $monthCursor->format('Y-m');
            $monthlyRevenue->push([
                'label' => $monthCursor->format('M Y'),
                'amount' => round((float) ($monthlyRevenueRows->get($monthKey, 0)), 2),
            ]);
            $monthCursor->addMonth();
        }

        $vehicleMixRows = TerminalParkingLog::query()
            ->selectRaw('terminal_vehicle_types.name as type_name, COUNT(terminal_parking_logs.id) as total')
            ->join('terminal_vehicles', 'terminal_vehicles.id', '=', 'terminal_parking_logs.terminal_vehicle_id')
            ->join('terminal_vehicle_types', 'terminal_vehicle_types.id', '=', 'terminal_vehicles.terminal_vehicle_type_id')
            ->whereDate('terminal_parking_logs.entry_at', '>=', $today->copy()->subDays(29)->toDateString())
            ->groupBy('terminal_vehicle_types.name')
            ->get();

        $typeLabels = [];
        $typeValues = [];
        foreach ($vehicleMixRows as $row) {
            $typeName = (string) ($row->type_name ?? 'Unknown');
            $typeLabels[] = $typeName;
            $typeValues[] = (int) ($row->total ?? 0);
        }

        if (count($typeLabels) === 0) {
            $typeLabels = ['No data'];
            $typeValues = [1];
        }

        return view('terminal.dashboard', [
            'todayEntries' => $todayEntries,
            'currentlyParked' => $currentlyParked,
            'readyForPayment' => $readyForPayment,
            'todayRevenue' => $todayRevenue,
            'activeVehicles' => $activeVehicles,
            'recentLogs' => $recentLogs,
            'dailyTrend' => $dailyTrend,
            'monthlyRevenue' => $monthlyRevenue,
            'typeLabels' => $typeLabels,
            'typeValues' => $typeValues,
        ]);
    }
}
