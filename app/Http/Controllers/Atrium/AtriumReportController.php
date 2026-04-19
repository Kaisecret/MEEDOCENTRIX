<?php

namespace App\Http\Controllers\Atrium;

use App\Http\Controllers\Controller;
use App\Models\AtriumEvent;
use App\Models\AtriumEventPayment;
use App\Models\AtriumSuppliesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AtriumReportController extends Controller
{
    public function index(Request $request): View
    {
        $report = trim((string) $request->query('report', 'booking'));
        if (! in_array($report, ['booking', 'collection', 'supplies'], true)) {
            $report = 'booking';
        }

        $range = trim((string) $request->query('range', 'month'));
        $fromInput = trim((string) $request->query('from', ''));
        $toInput = trim((string) $request->query('to', ''));

        [$rangeStart, $rangeEnd, $rangeLabel] = $this->resolveRange($range, $fromInput, $toInput);

        $data = [
            'report' => $report,
            'range' => $range,
            'rangeLabel' => $rangeLabel,
            'from' => $fromInput,
            'to' => $toInput,
        ];

        if ($report === 'booking') {
            $data += $this->bookingReport($rangeStart, $rangeEnd);
        } elseif ($report === 'collection') {
            $data += $this->collectionReport($rangeStart, $rangeEnd);
        } else {
            $data += $this->suppliesReport($rangeStart, $rangeEnd);
        }

        return view('atrium.reports', $data);
    }

    private function bookingReport(?Carbon $start, ?Carbon $end): array
    {
        $query = AtriumEvent::query()->with('functionHall:id,name,code');
        if ($start && $end) {
            $query->whereBetween('date_of_event', [$start->toDateString(), $end->toDateString()]);
        }
        $events = $query->orderBy('date_of_event')->get();

        $summary = [
            'total' => $events->count(),
            'reserved' => $events->where('booking_status', 'reserved')->count(),
            'confirmed' => $events->where('booking_status', 'confirmed')->count(),
            'completed' => $events->where('booking_status', 'completed')->count(),
            'cancelled' => $events->where('booking_status', 'cancelled')->count(),
            'total_due' => (float) $events->sum('actual_due'),
        ];

        return [
            'events' => $events,
            'summary' => $summary,
        ];
    }

    private function collectionReport(?Carbon $start, ?Carbon $end): array
    {
        $query = AtriumEventPayment::query()->with(['event.functionHall:id,name,code', 'recordedBy:id,name']);
        if ($start && $end) {
            $query->whereBetween('date_of_payment', [$start->toDateString(), $end->toDateString()]);
        }
        $payments = $query->orderBy('date_of_payment')->get();

        $summary = [
            'total' => $payments->count(),
            'collected' => (float) $payments->sum('payment_amount'),
            'paid' => $payments->where('payment_status', 'paid')->count(),
            'partial' => $payments->where('payment_status', 'partial')->count(),
            'unpaid' => $payments->where('payment_status', 'unpaid')->count(),
        ];

        return [
            'payments' => $payments,
            'summary' => $summary,
        ];
    }

    private function suppliesReport(?Carbon $start, ?Carbon $end): array
    {
        $query = AtriumSuppliesOrder::query()->with(['event.functionHall:id,name,code', 'requestedBy:id,name']);
        if ($start && $end) {
            $query->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
        }
        $orders = $query->orderBy('created_at')->get();

        $summary = [
            'total' => $orders->count(),
            'pending' => $orders->where('request_status', 'pending')->count(),
            'approved' => $orders->where('request_status', 'approved')->count(),
            'fulfilled' => $orders->where('request_status', 'fulfilled')->count(),
            'rejected' => $orders->where('request_status', 'rejected')->count(),
        ];

        return [
            'orders' => $orders,
            'summary' => $summary,
        ];
    }

    /**
     * @return array{0: ?\Illuminate\Support\Carbon, 1: ?\Illuminate\Support\Carbon, 2: string}
     */
    private function resolveRange(string $range, string $fromInput, string $toInput): array
    {
        $now = Carbon::now();
        switch ($range) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today (' . $now->format('M d, Y') . ')'];
            case 'week':
                $s = $now->copy()->startOfWeek(Carbon::MONDAY);
                $e = $now->copy()->endOfWeek(Carbon::SUNDAY);
                return [$s, $e, 'This Week (' . $s->format('M d') . ' – ' . $e->format('M d, Y') . ')'];
            case 'month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month (' . $now->format('F Y') . ')'];
            case 'all':
                return [null, null, 'All Dates'];
            case 'custom':
                $s = $fromInput !== '' ? Carbon::parse($fromInput)->startOfDay() : null;
                $e = $toInput !== '' ? Carbon::parse($toInput)->endOfDay() : null;
                $label = 'Custom';
                if ($s && $e) {
                    $label = $s->format('M d, Y') . ' – ' . $e->format('M d, Y');
                }
                return [$s, $e, $label];
            default:
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'This Month (' . $now->format('F Y') . ')'];
        }
    }
}
