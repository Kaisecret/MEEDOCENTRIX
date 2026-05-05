<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use App\Models\TerminalQuickPayment;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TerminalDashboardController extends Controller
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
            $rangeStart = $today->copy()->startOfMonth()->startOfDay();
            $rangeEnd = $today->copy()->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'This Month';
        }

        $displayRange = $rangeStart->isSameDay($rangeEnd)
            ? $rangeStart->format('F j, Y')
            : $rangeStart->format('F j, Y') . ' to ' . $rangeEnd->format('F j, Y');

        $paidBaseQuery = TerminalQuickPayment::query()
            ->where('is_paid', true)
            ->whereNotNull('paid_at')
            ->whereNotNull('ticket_number')
            ->where('ticket_number', '<>', '')
            ->whereNotNull('route_code')
            ->where('route_code', '<>', '')
            ->whereBetween('paid_at', [$rangeStart->copy()->startOfDay(), $rangeEnd->copy()->endOfDay()]);

        $filterRevenue = (float) (clone $paidBaseQuery)->sum('total_payment');

        $filterPaidCount = (clone $paidBaseQuery)->count();

        $yearRevenue = (float) TerminalQuickPayment::query()
            ->where('is_paid', true)
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', '>=', $today->copy()->startOfYear()->toDateString())
            ->whereNotNull('ticket_number')
            ->where('ticket_number', '<>', '')
            ->whereNotNull('route_code')
            ->where('route_code', '<>', '')
            ->sum('total_payment');

        $pendingBaseQuery = TerminalQuickPayment::query()
            ->where('is_paid', false)
            ->whereNotNull('ticket_number')
            ->where('ticket_number', '<>', '')
            ->whereNotNull('route_code')
            ->where('route_code', '<>', '')
            ->whereBetween('payment_date', [$rangeStart->copy()->startOfDay(), $rangeEnd->copy()->endOfDay()]);

        $pendingCount = (clone $pendingBaseQuery)->count();
        $pendingAmount = (float) (clone $pendingBaseQuery)->sum('total_payment');

        $avgTicket = (float) (clone $paidBaseQuery)->avg('total_payment');

        $recentPaid = (clone $paidBaseQuery)
            ->with(['recordedBy:id,name', 'paidBy:id,name'])
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get();

        $trendStart = $rangeEnd->copy()->subDays(30)->startOfDay();
        if ($trendStart->lt($rangeStart)) {
            $trendStart = $rangeStart->copy()->startOfDay();
        }

        $dailyRows = (clone $paidBaseQuery)
            ->selectRaw('DATE(paid_at) as day_key, SUM(total_payment) as revenue_total, COUNT(*) as tx_total')
            ->whereDate('paid_at', '>=', $trendStart->toDateString())
            ->groupBy('day_key')
            ->orderBy('day_key')
            ->get()
            ->keyBy('day_key');

        $dailyRevenueTrend = collect();
        $trendPeriod = CarbonPeriod::create($trendStart, $rangeEnd->copy()->endOfDay());
        foreach ($trendPeriod as $day) {
            $key = $day->toDateString();
            $dailyRevenueTrend->push([
                'label' => $day->format('M d'),
                'revenue' => round((float) ($dailyRows->get($key)->revenue_total ?? 0), 2),
                'transactions' => (int) ($dailyRows->get($key)->tx_total ?? 0),
            ]);
        }

        $monthlyStart = $rangeStart->copy()->startOfMonth();
        $monthlyRows = (clone $paidBaseQuery)
            ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as month_key, SUM(total_payment) as total")
            ->whereDate('paid_at', '>=', $monthlyStart->toDateString())
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()
            ->keyBy('month_key');

        $monthlyRevenue = collect();
        $monthCursor = $monthlyStart->copy();
        $monthEnd = $rangeEnd->copy()->startOfMonth();
        while ($monthCursor->lte($monthEnd)) {
            $monthKey = $monthCursor->format('Y-m');
            $monthlyRevenue->push([
                'label' => $monthCursor->format('M Y'),
                'amount' => round((float) ($monthlyRows->get($monthKey)->total ?? 0), 2),
            ]);
            $monthCursor->addMonth();
        }

        $routePerformance = (clone $paidBaseQuery)
            ->selectRaw('route_name, SUM(total_payment) as total_revenue, COUNT(*) as total_transactions')
            ->whereNotNull('route_name')
            ->where('route_name', '<>', '')
            ->groupBy('route_name')
            ->orderByDesc('total_revenue')
            ->limit(8)
            ->get();

        return view('terminal.dashboard', [
            'filterRevenue' => $filterRevenue,
            'yearRevenue' => $yearRevenue,
            'filterPaidCount' => $filterPaidCount,
            'pendingCount' => $pendingCount,
            'pendingAmount' => $pendingAmount,
            'avgTicket' => $avgTicket,
            'recentPaid' => $recentPaid,
            'dailyRevenueTrend' => $dailyRevenueTrend,
            'monthlyRevenue' => $monthlyRevenue,
            'routePerformance' => $routePerformance,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filterLabel' => $filterLabel,
            'displayRange' => $displayRange,
        ]);
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
}
