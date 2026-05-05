<?php

namespace App\Http\Controllers\Cemetery;

use App\Http\Controllers\Controller;
use App\Models\CemeteryOccupantRecord;
use App\Models\CemeteryPaymentCollection;
use App\Models\CemeteryPlot;
use App\Models\CemeteryServiceLog;
use App\Models\CemeteryTransaction;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CemeteryDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $period = strtolower((string) $request->query('period', 'month'));
        $allowedPeriods = ['today', 'week', 'month', 'range'];
        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'month';
        }

        $parsedFrom = $this->parseDate($request->query('date_from'));
        $parsedTo = $this->parseDate($request->query('date_to'));

        if ($period === 'today') {
            $rangeStart = $today->copy()->startOfDay();
            $rangeEnd = $today->copy()->endOfDay();
            $dateFrom = $today->toDateString();
            $dateTo = $today->toDateString();
            $filterLabel = 'Today';
        } elseif ($period === 'week') {
            $rangeStart = $today->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $rangeEnd = $today->copy()->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'This Week';
        } elseif ($period === 'range' && $parsedFrom && $parsedTo && $parsedFrom->lte($parsedTo)) {
            $rangeStart = $parsedFrom->copy()->startOfDay();
            $rangeEnd = $parsedTo->copy()->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'Custom Range';
        } else {
            $period = 'month';
            $rangeStart = $today->copy()->startOfMonth();
            $rangeEnd = $today->copy()->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'This Month';
        }

        $displayRange = $rangeStart->isSameDay($rangeEnd)
            ? $rangeStart->format('F j, Y')
            : $rangeStart->format('F j, Y') . ' to ' . $rangeEnd->format('F j, Y');

        $transactionRangeQuery = function ($query) use ($rangeStart, $rangeEnd): void {
            $query->whereBetween('transaction_date', [$rangeStart, $rangeEnd]);
        };

        $serviceRangeQuery = function ($query) use ($rangeStart, $rangeEnd): void {
            $query
                ->whereDate('service_date', '>=', $rangeStart->toDateString())
                ->whereDate('service_date', '<=', $rangeEnd->toDateString());
        };

        $paymentRangeQuery = function ($query) use ($rangeStart, $rangeEnd): void {
            $query
                ->whereDate('payment_date', '>=', $rangeStart->toDateString())
                ->whereDate('payment_date', '<=', $rangeEnd->toDateString());
        };

        $transactionDailyRaw = CemeteryTransaction::query()
            ->selectRaw('DATE(transaction_date) as activity_day')
            ->selectRaw('COUNT(*) as total')
            ->where($transactionRangeQuery)
            ->groupBy('activity_day')
            ->orderBy('activity_day')
            ->get()
            ->keyBy('activity_day');

        $serviceDailyRaw = CemeteryServiceLog::query()
            ->selectRaw('DATE(service_date) as activity_day')
            ->selectRaw('COUNT(*) as total')
            ->where($serviceRangeQuery)
            ->groupBy('activity_day')
            ->orderBy('activity_day')
            ->get()
            ->keyBy('activity_day');

        $paymentDailyRaw = CemeteryPaymentCollection::query()
            ->selectRaw('DATE(payment_date) as activity_day')
            ->selectRaw('COALESCE(SUM(amount_paid), 0) as total_amount')
            ->whereNotNull('payment_date')
            ->where($paymentRangeQuery)
            ->groupBy('activity_day')
            ->orderBy('activity_day')
            ->get()
            ->keyBy('activity_day');

        $activityStats = collect();
        foreach (CarbonPeriod::create($rangeStart->copy()->startOfDay(), $rangeEnd->copy()->startOfDay()) as $day) {
            $key = $day->toDateString();
            $tx = $transactionDailyRaw->get($key);
            $service = $serviceDailyRaw->get($key);
            $payment = $paymentDailyRaw->get($key);

            $activityStats->push([
                'label' => $day->format('D'),
                'date' => $day->format('m/d'),
                'transactions' => (int) ($tx->total ?? 0),
                'services' => (int) ($service->total ?? 0),
                'payments' => round((float) ($payment->total_amount ?? 0), 2),
            ]);
        }
        if ($activityStats->count() > 31) {
            $activityStats = $activityStats->slice(-31)->values();
        }

        $statusSeed = collect([
            'pending' => 0,
            'partial' => 0,
            'paid' => 0,
            'overdue' => 0,
            'cancelled' => 0,
            'unpaid' => 0,
        ]);
        $statusTotals = CemeteryTransaction::query()
            ->where($transactionRangeQuery)
            ->selectRaw("LOWER(COALESCE(status, 'pending')) as status_key")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status_key')
            ->pluck('total', 'status_key')
            ->map(fn ($value) => (int) $value);
        $statusCounts = $statusSeed->merge($statusTotals);

        $monthStart = $rangeStart->copy()->startOfMonth();
        $monthEnd = $rangeEnd->copy()->startOfMonth();
        $paymentsByMonth = CemeteryPaymentCollection::query()
            ->whereNotNull('payment_date')
            ->where($paymentRangeQuery)
            ->selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as activity_month")
            ->selectRaw('COALESCE(SUM(amount_paid), 0) as total_amount')
            ->groupBy('activity_month')
            ->pluck('total_amount', 'activity_month')
            ->map(fn ($value) => round((float) $value, 2));

        $monthlyCollections = collect();
        $monthCursor = $monthStart->copy();
        while ($monthCursor->lte($monthEnd)) {
            $key = $monthCursor->format('Y-m');
            $monthlyCollections->push([
                'label' => $monthCursor->format('M Y'),
                'amount' => (float) ($paymentsByMonth->get($key, 0)),
            ]);
            $monthCursor->addMonth();
        }

        $recentTransactions = CemeteryTransaction::query()
            ->with(['site:id,site_name', 'transactionType:id,type_name'])
            ->where($transactionRangeQuery)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $servicesPeriodCount = CemeteryServiceLog::query()->where($serviceRangeQuery)->count();
        $transactionsPeriodCount = CemeteryTransaction::query()->where($transactionRangeQuery)->count();
        $paymentsPeriodAmount = (float) CemeteryPaymentCollection::query()
            ->where($paymentRangeQuery)
            ->sum('amount_paid');

        return view('cemetery.dashboard', [
            'summary' => [
                'total_occupants' => CemeteryOccupantRecord::query()->count(),
                'occupied_plots' => CemeteryPlot::query()->where('is_occupied', true)->count(),
                'available_plots' => CemeteryPlot::query()->where('is_active', true)->where('is_occupied', false)->count(),
                'services_period' => $servicesPeriodCount,
                'transactions_period' => $transactionsPeriodCount,
                'payments_period' => $paymentsPeriodAmount,
                'total_collected' => (float) CemeteryPaymentCollection::query()->sum('amount_paid'),
                'overdue_maintenance' => CemeteryOccupantRecord::query()->where('maintenance_fee_status', 'overdue')->count(),
            ],
            'recentTransactions' => $recentTransactions,
            'activityStats' => $activityStats,
            'statusCounts' => $statusCounts,
            'monthlyCollections' => $monthlyCollections,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filterLabel' => $filterLabel,
            'displayRange' => $displayRange,
        ]);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $raw = trim($value);

        try {
            return Carbon::createFromFormat('Y-m-d', $raw)->startOfDay();
        } catch (\Throwable) {
            try {
                return Carbon::parse($raw)->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }
    }
}
