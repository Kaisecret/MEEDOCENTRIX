<?php

namespace App\Http\Controllers\Atrium;

use App\Http\Controllers\Controller;
use App\Models\AtriumEvent;
use App\Models\AtriumEventPayment;
use App\Models\AtriumSuppliesOrder;
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

        $totalEvents = AtriumEvent::query()->count();
        $eventsThisMonth = AtriumEvent::query()
            ->whereBetween('date_of_event', [$monthStart, $monthEnd])
            ->count();
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
            'pendingSupplies' => $pendingSupplies,
            'nextEvents' => $nextEvents,
        ]);
    }
}
