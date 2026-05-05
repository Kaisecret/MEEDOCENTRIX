<?php

namespace App\Http\Controllers\Fishport;

use App\Http\Controllers\Controller;
use App\Models\FishportLog;
use App\Models\FishportVessel;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FishportDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $period = strtolower((string) $request->query('period', 'month'));
        $allowedPeriods = ['today', 'week', 'month', 'range'];
        if (!in_array($period, $allowedPeriods, true)) {
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

        $dateRangeQuery = function ($query) use ($rangeStart, $rangeEnd): void {
            $query
                ->whereDate('log_date', '>=', $rangeStart->toDateString())
                ->whereDate('log_date', '<=', $rangeEnd->toDateString());
        };

        $paidAtRangeQuery = function ($query) use ($rangeStart, $rangeEnd): void {
            $query->whereBetween('paid_at', [$rangeStart->copy()->startOfDay(), $rangeEnd->copy()->endOfDay()]);
        };

        $daysInRange = max(1, $rangeStart->diffInDays($rangeEnd) + 1);
        $previousStart = $rangeStart->copy()->subDays($daysInRange);
        $previousEnd = $rangeStart->copy()->subDay()->endOfDay();

        $vesselsTodayCount = FishportLog::query()->where($dateRangeQuery)->count();
        $vesselsTodayPrevCount = FishportLog::query()
            ->whereDate('log_date', '>=', $previousStart->toDateString())
            ->whereDate('log_date', '<=', $previousEnd->toDateString())
            ->count();

        $notPaidCount = FishportLog::query()->where($dateRangeQuery)->where('is_paid', false)->count();

        $notPaidAmount = FishportLog::query()
            ->where($dateRangeQuery)
            ->where('is_paid', false)
            ->with(['paymentRecord:id,fishport_log_id,total_amount'])
            ->withSum('payments', 'total')
            ->get()
            ->sum(fn (FishportLog $log) => $this->resolveLogAmount($log));

        $paidTodayAmount = FishportLog::query()
            ->where('is_paid', true)
            ->where($paidAtRangeQuery)
            ->with(['paymentRecord:id,fishport_log_id,total_amount'])
            ->withSum('payments', 'total')
            ->get()
            ->sum(fn (FishportLog $log) => $this->resolveLogAmount($log));

        $totalRevenue = FishportLog::query()
            ->where($dateRangeQuery)
            ->with(['paymentRecord:id,fishport_log_id,total_amount'])
            ->withSum('payments', 'total')
            ->get()
            ->sum(fn (FishportLog $log) => $this->resolveLogAmount($log));

        $registeredVessels = FishportVessel::query()->where('is_active', true)->count();

        $rawDailyActivity = FishportLog::query()
            ->selectRaw("DATE(log_date) as log_day")
            ->selectRaw("SUM(CASE WHEN arr_dep = 'ARR' THEN 1 ELSE 0 END) as arrivals")
            ->selectRaw("SUM(CASE WHEN arr_dep = 'DEP' THEN 1 ELSE 0 END) as departures")
            ->where($dateRangeQuery)
            ->groupBy('log_day')
            ->orderBy('log_day')
            ->get()
            ->keyBy('log_day');

        $dailyStats = collect();
        $periodDays = CarbonPeriod::create($rangeStart->copy()->startOfDay(), $rangeEnd->copy()->startOfDay());
        foreach ($periodDays as $day) {
            $key = $day->toDateString();
            $item = $rawDailyActivity->get($key);

            $dailyStats->push([
                'label' => $day->format('D'),
                'date' => $day->format('m/d'),
                'arrivals' => (int) ($item->arrivals ?? 0),
                'departures' => (int) ($item->departures ?? 0),
            ]);
        }

        if ($dailyStats->count() > 31) {
            $dailyStats = $dailyStats->slice(-31)->values();
        }

        $paidLogsInRange = FishportLog::query()
            ->where('is_paid', true)
            ->where($paidAtRangeQuery)
            ->with(['paymentRecord:id,fishport_log_id,total_amount'])
            ->withSum('payments', 'total')
            ->get();

        $monthlyRevenue = collect();
        $monthCursor = $rangeStart->copy()->startOfMonth();
        $monthEnd = $rangeEnd->copy()->startOfMonth();
        $revenueByMonth = $paidLogsInRange
            ->filter(fn (FishportLog $log) => $log->paid_at !== null)
            ->groupBy(fn (FishportLog $log) => Carbon::parse($log->paid_at)->format('Y-m'))
            ->map(fn ($logs) => round((float) $logs->sum(fn (FishportLog $log) => $this->resolveLogAmount($log)), 2));

        while ($monthCursor->lte($monthEnd)) {
            $ym = $monthCursor->format('Y-m');
            $monthlyRevenue->push([
                'label' => $monthCursor->format('M Y'),
                'revenue' => (float) ($revenueByMonth->get($ym, 0)),
            ]);
            $monthCursor->addMonth();
        }

        $notPaidLogs = FishportLog::query()
            ->where($dateRangeQuery)
            ->where('is_paid', false)
            ->with(['vessel:id,name', 'origin:id,name', 'payments', 'paymentRecord:id,fishport_log_id,total_amount'])
            ->withSum('payments', 'total')
            ->orderByDesc('log_date')
            ->orderByDesc('log_time')
            ->limit(8)
            ->get()
            ->map(function (FishportLog $log) {
                return [
                    'log_number' => $log->log_number,
                    'vessel' => $log->vessel?->name ?? '-',
                    'origin' => $log->origin?->name ?? '-',
                    'arr_dep' => $log->arr_dep,
                    'log_date' => optional($log->log_date)->format('m/d/Y'),
                    'grand_total' => round($this->resolveLogAmount($log), 2),
                ];
            });

        $arrCount = (int) $dailyStats->sum('arrivals');
        $depCount = (int) $dailyStats->sum('departures');
        $monthTotal = FishportLog::query()->where($dateRangeQuery)->count();
        $monthPaid = FishportLog::query()->where($dateRangeQuery)->where('is_paid', true)->count();
        $paidPercent = $monthTotal > 0 ? (int) round($monthPaid / $monthTotal * 100) : 0;

        return view('fishport.dashboard', compact(
            'vesselsTodayCount',
            'vesselsTodayPrevCount',
            'notPaidCount',
            'notPaidAmount',
            'paidTodayAmount',
            'totalRevenue',
            'registeredVessels',
            'dailyStats',
            'monthlyRevenue',
            'notPaidLogs',
            'arrCount',
            'depCount',
            'monthTotal',
            'monthPaid',
            'paidPercent',
            'period',
            'dateFrom',
            'dateTo',
            'filterLabel',
            'displayRange',
        ));
    }

    private function parseDate(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', trim($value))->startOfDay();
        } catch (\Throwable) {
            try {
                return Carbon::parse(trim($value))->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }
    }

    private function resolveLogAmount(FishportLog $log): float
    {
        $paymentsTotal = (float) ($log->payments_sum_total ?? 0);
        if ($paymentsTotal > 0) {
            return $paymentsTotal;
        }

        return round((float) ($log->paymentRecord?->total_amount ?? 0), 2);
    }
}
