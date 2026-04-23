<?php

namespace App\Http\Controllers\Atrium;

use App\Http\Controllers\Controller;
use App\Models\AtriumEvent;
use App\Models\AtriumEventPayment;
use App\Models\AtriumSuppliesOrder;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AtriumDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $eventsThisMonthCollection = AtriumEvent::query()
            ->whereBetween('date_of_event', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get(['date_of_event', 'booking_status', 'actual_due']);

        $totalEvents = AtriumEvent::query()->count();
        $eventsThisMonth = $eventsThisMonthCollection->count();
        $upcomingEvents = AtriumEvent::query()
            ->whereDate('date_of_event', '>=', $today)
            ->where('booking_status', '!=', 'cancelled')
            ->count();
        $completedEvents = AtriumEvent::query()
            ->where('booking_status', 'completed')
            ->count();

        $totalCollected = (float) AtriumEventPayment::query()->sum('payment_amount');
        $collectedThisMonth = (float) AtriumEventPayment::query()
            ->whereBetween('date_of_payment', [$monthStart, $monthEnd])
            ->sum('payment_amount');

        $totalDue = (float) AtriumEvent::query()->sum('actual_due');
        $outstanding = max(0.0, $totalDue - $totalCollected);
        $collectionProgressPercent = $totalDue > 0
            ? (int) min(100, round(($totalCollected / $totalDue) * 100))
            : 0;
        $outstandingPercent = 100 - $collectionProgressPercent;

        $statusCounts = [
            'reserved' => (int) $eventsThisMonthCollection->where('booking_status', 'reserved')->count(),
            'confirmed' => (int) $eventsThisMonthCollection->where('booking_status', 'confirmed')->count(),
            'completed' => (int) $eventsThisMonthCollection->where('booking_status', 'completed')->count(),
            'cancelled' => (int) $eventsThisMonthCollection->where('booking_status', 'cancelled')->count(),
        ];

        $dailyRaw = AtriumEvent::query()
            ->selectRaw('DATE(date_of_event) as event_day')
            ->selectRaw('COUNT(*) as bookings_count')
            ->selectRaw("SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->whereDate('date_of_event', '>=', $monthStart->toDateString())
            ->whereDate('date_of_event', '<=', $today->toDateString())
            ->groupBy('event_day')
            ->orderBy('event_day')
            ->get()
            ->keyBy('event_day');

        $dailyBookingStats = collect();
        foreach (CarbonPeriod::create($monthStart->copy()->startOfDay(), $today->copy()->startOfDay()) as $day) {
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

        $revenueStart = $today->copy()->startOfMonth()->subMonths(5);
        $revenueEnd = $today->copy()->endOfMonth();
        $revenueRaw = AtriumEventPayment::query()
            ->selectRaw("DATE_FORMAT(date_of_payment, '%Y-%m') as ym")
            ->selectRaw('SUM(payment_amount) as total_amount')
            ->whereDate('date_of_payment', '>=', $revenueStart->toDateString())
            ->whereDate('date_of_payment', '<=', $revenueEnd->toDateString())
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->pluck('total_amount', 'ym');

        $monthlyRevenue = collect();
        $monthCursor = $revenueStart->copy()->startOfMonth();
        $monthLimit = $today->copy()->startOfMonth();
        while ($monthCursor->lte($monthLimit)) {
            $monthKey = $monthCursor->format('Y-m');
            $monthlyRevenue->push([
                'label' => $monthCursor->format('M Y'),
                'amount' => round((float) ($revenueRaw[$monthKey] ?? 0), 2),
            ]);

            $monthCursor->addMonth();
        }

        $pendingSupplies = AtriumSuppliesOrder::query()
            ->where('request_status', 'pending')
            ->count();

        $nextEvents = AtriumEvent::query()
            ->with('functionHall:id,name,code')
            ->whereDate('date_of_event', '>=', $today)
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
            'pendingSupplies' => $pendingSupplies,
            'nextEvents' => $nextEvents,
        ]);
    }
}
