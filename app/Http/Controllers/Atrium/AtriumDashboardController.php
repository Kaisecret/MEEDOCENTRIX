<?php

namespace App\Http\Controllers\Atrium;

use App\Http\Controllers\Controller;
use App\Models\AtriumEvent;
use App\Models\AtriumEventPayment;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AtriumDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $period = strtolower((string) $request->query('period', 'week'));
        $allowedPeriods = ['today', 'week', 'month', 'range'];
        if (! in_array($period, $allowedPeriods, true)) {
            $period = 'week';
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
            $rangeEnd = $today->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'This Week';
        } elseif ($period === 'range' && $parsedFrom && $parsedTo && $parsedFrom->lte($parsedTo)) {
            $rangeStart = $parsedFrom->copy()->startOfDay();
            $rangeEnd = $parsedTo->copy()->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'Custom Range';
        } elseif ($period === 'month') {
            $rangeStart = $today->copy()->startOfMonth();
            $rangeEnd = $today->copy()->endOfMonth()->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'This Month';
        } else {
            $period = 'week';
            $rangeStart = $today->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $rangeEnd = $today->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            $dateFrom = $rangeStart->toDateString();
            $dateTo = $rangeEnd->toDateString();
            $filterLabel = 'This Week';
        }

        $displayRange = $rangeStart->isSameDay($rangeEnd)
            ? $rangeStart->format('F j, Y')
            : $rangeStart->format('F j, Y') . ' to ' . $rangeEnd->format('F j, Y');

        $eventsInRangeCollection = AtriumEvent::query()
            ->whereDate('date_of_event', '>=', $rangeStart->toDateString())
            ->whereDate('date_of_event', '<=', $rangeEnd->toDateString())
            ->get(['date_of_event', 'booking_status', 'actual_due']);

        $totalEvents = $eventsInRangeCollection->count();
        $eventsThisMonth = $eventsInRangeCollection->count();
        // Upcoming should always reflect true future reservations, not just the current summary range.
        $upcomingEvents = AtriumEvent::query()
            ->whereDate('date_of_event', '>=', $today)
            ->where('booking_status', '!=', 'cancelled')
            ->count();
        $completedEvents = (int) $eventsInRangeCollection->where('booking_status', 'completed')->count();

        $totalCollected = (float) AtriumEventPayment::query()
            ->whereDate('date_of_payment', '>=', $rangeStart->toDateString())
            ->whereDate('date_of_payment', '<=', $rangeEnd->toDateString())
            ->sum('payment_amount');
        $collectedThisMonth = (float) AtriumEventPayment::query()
            ->whereDate('date_of_payment', '>=', $rangeStart->toDateString())
            ->whereDate('date_of_payment', '<=', $rangeEnd->toDateString())
            ->sum('payment_amount');

        $totalDue = (float) $eventsInRangeCollection->sum('actual_due');
        $outstanding = max(0.0, $totalDue - $totalCollected);
        $collectionProgressPercent = $totalDue > 0
            ? (int) min(100, round(($totalCollected / $totalDue) * 100))
            : 0;
        $outstandingPercent = 100 - $collectionProgressPercent;

        $statusCounts = [
            'reserved' => (int) $eventsInRangeCollection->where('booking_status', 'reserved')->count(),
            'confirmed' => (int) $eventsInRangeCollection->where('booking_status', 'confirmed')->count(),
            'completed' => (int) $eventsInRangeCollection->where('booking_status', 'completed')->count(),
            'cancelled' => (int) $eventsInRangeCollection->where('booking_status', 'cancelled')->count(),
        ];

        $dailyRaw = AtriumEvent::query()
            ->selectRaw('DATE(date_of_event) as event_day')
            ->selectRaw('COUNT(*) as bookings_count')
            ->selectRaw("SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->whereDate('date_of_event', '>=', $rangeStart->toDateString())
            ->whereDate('date_of_event', '<=', $rangeEnd->toDateString())
            ->groupBy('event_day')
            ->orderBy('event_day')
            ->get()
            ->keyBy('event_day');

        $dailyBookingStats = collect();
        foreach (CarbonPeriod::create($rangeStart->copy()->startOfDay(), $rangeEnd->copy()->startOfDay()) as $day) {
            $dayKey = $day->toDateString();
            $dailyItem = $dailyRaw->get($dayKey);

            $dailyBookingStats->push([
                'label' => $day->format('M d'),
                'bookings' => (int) ($dailyItem->bookings_count ?? 0),
                'completed' => (int) ($dailyItem->completed_count ?? 0),
            ]);
        }

        if ($dailyBookingStats->count() > 31) {
            $dailyBookingStats = $dailyBookingStats->slice(-31)->values();
        }

        $rangeDays = $rangeStart->copy()->startOfDay()->diffInDays($rangeEnd->copy()->startOfDay()) + 1;
        if ($rangeDays <= 45) {
            $revenueGranularity = 'day';
        } elseif ($rangeDays <= 180) {
            $revenueGranularity = 'week';
        } else {
            $revenueGranularity = 'month';
        }

        if ($revenueGranularity === 'day') {
            $revenueRaw = AtriumEventPayment::query()
                ->selectRaw("DATE(date_of_payment) as bucket")
                ->selectRaw('SUM(payment_amount) as total_amount')
                ->whereDate('date_of_payment', '>=', $rangeStart->toDateString())
                ->whereDate('date_of_payment', '<=', $rangeEnd->toDateString())
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get()
                ->pluck('total_amount', 'bucket');

            $monthlyRevenue = collect();
            foreach (CarbonPeriod::create($rangeStart->copy()->startOfDay(), $rangeEnd->copy()->startOfDay()) as $day) {
                $key = $day->toDateString();
                $monthlyRevenue->push([
                    'label' => $day->format('M d'),
                    'amount' => round((float) ($revenueRaw[$key] ?? 0), 2),
                ]);
            }
        } elseif ($revenueGranularity === 'week') {
            $revenueRaw = AtriumEventPayment::query()
                ->selectRaw("YEARWEEK(date_of_payment, 3) as bucket")
                ->selectRaw('SUM(payment_amount) as total_amount')
                ->whereDate('date_of_payment', '>=', $rangeStart->toDateString())
                ->whereDate('date_of_payment', '<=', $rangeEnd->toDateString())
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get()
                ->pluck('total_amount', 'bucket');

            $monthlyRevenue = collect();
            $weekCursor = $rangeStart->copy()->startOfWeek(Carbon::MONDAY);
            $weekLimit = $rangeEnd->copy()->startOfWeek(Carbon::MONDAY);
            while ($weekCursor->lte($weekLimit)) {
                $key = (int) $weekCursor->format('o') . str_pad((string) $weekCursor->isoWeek(), 2, '0', STR_PAD_LEFT);
                $monthlyRevenue->push([
                    'label' => $weekCursor->format('M d'),
                    'amount' => round((float) ($revenueRaw[$key] ?? 0), 2),
                ]);
                $weekCursor->addWeek();
            }
        } else {
            $revenueStart = $rangeStart->copy()->startOfMonth();
            $revenueRaw = AtriumEventPayment::query()
                ->selectRaw("DATE_FORMAT(date_of_payment, '%Y-%m') as ym")
                ->selectRaw('SUM(payment_amount) as total_amount')
                ->whereDate('date_of_payment', '>=', $revenueStart->toDateString())
                ->whereDate('date_of_payment', '<=', $rangeEnd->toDateString())
                ->groupBy('ym')
                ->orderBy('ym')
                ->get()
                ->pluck('total_amount', 'ym');

            $monthlyRevenue = collect();
            $monthCursor = $revenueStart->copy()->startOfMonth();
            $monthLimit = $rangeEnd->copy()->startOfMonth();
            while ($monthCursor->lte($monthLimit)) {
                $monthKey = $monthCursor->format('Y-m');
                $monthlyRevenue->push([
                    'label' => $monthCursor->format('M Y'),
                    'amount' => round((float) ($revenueRaw[$monthKey] ?? 0), 2),
                ]);
                $monthCursor->addMonth();
            }
        }

        $nextEvents = AtriumEvent::query()
            ->with('functionHall:id,name,code')
            ->whereDate('date_of_event', '>=', $rangeStart->toDateString())
            ->whereDate('date_of_event', '<=', $rangeEnd->toDateString())
            ->where('booking_status', '!=', 'cancelled')
            ->orderBy('date_of_event')
            ->limit(6)
            ->get();

        return view('atrium.dashboard', [
            'totalEvents' => $totalEvents,
            'eventsThisMonth' => $eventsThisMonth,
            'upcomingEvents' => $upcomingEvents,
            'completedEvents' => $completedEvents,
            'totalCollected' => $totalCollected,
            'collectedThisMonth' => $collectedThisMonth,
            'totalDue' => $totalDue,
            'outstanding' => $outstanding,
            'collectionProgressPercent' => $collectionProgressPercent,
            'outstandingPercent' => $outstandingPercent,
            'statusCounts' => $statusCounts,
            'dailyBookingStats' => $dailyBookingStats,
            'monthlyRevenue' => $monthlyRevenue,
            'nextEvents' => $nextEvents,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'filterLabel' => $filterLabel,
            'displayRange' => $displayRange,
        ]);
    }

    private function parseDate(null|string $value): ?Carbon
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
