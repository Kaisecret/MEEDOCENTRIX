<?php

namespace App\Http\Controllers\Atrium;

use App\Http\Controllers\Controller;
use App\Models\AtriumEvent;
use App\Models\AtriumEventPayment;
use App\Models\AtriumSuppliesOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AtriumReportController extends Controller
{
    private const PDF_MAX_ROWS = 100;

    public function index(Request $request): View
    {
        [$report, $period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveFilters($request);
        $payload = $this->buildReportPayload($report, $rangeStart, $rangeEnd);

        return view('atrium.reports', [
            'report' => $report,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'rangeLabel' => $rangeLabel,
            ...$payload,
        ]);
    }

    public function preview(Request $request): View
    {
        [$report, $period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveFilters($request);
        $payload = $this->buildReportPayload($report, $rangeStart, $rangeEnd);

        $previewRows = $payload['rows']->take(self::PDF_MAX_ROWS)->values();

        return view('atrium.reports_pdf', [
            'report' => $report,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'rangeLabel' => $rangeLabel,
            'generatedAt' => now(),
            ...$payload,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
            'pdfDisplayedRows' => $previewRows->count(),
            'pdfTotalRows' => (int) $payload['rows']->count(),
            'rows' => $previewRows,
        ]);
    }

    public function pdf(Request $request)
    {
        [$report, $period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveFilters($request);
        $payload = $this->buildReportPayload($report, $rangeStart, $rangeEnd);

        $pdfRows = $payload['rows']->take(self::PDF_MAX_ROWS)->values();
        $filename = 'atrium-' . $report . '-report-' . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.pdf';

        return Pdf::loadView('atrium.reports_pdf', [
            'report' => $report,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'rangeLabel' => $rangeLabel,
            'generatedAt' => now(),
            ...$payload,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
            'pdfDisplayedRows' => $pdfRows->count(),
            'pdfTotalRows' => (int) $payload['rows']->count(),
            'rows' => $pdfRows,
        ])->download($filename);
    }

    /**
     * @return array{0:string,1:string,2:Carbon,3:Carbon,4:string,5:string,6:string}
     */
    private function resolveFilters(Request $request): array
    {
        $report = $this->resolveReportType((string) $request->query('report', 'booking'));
        [$period, $start, $end, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);

        return [$report, $period, $start, $end, $dateFrom, $dateTo, $rangeLabel];
    }

    private function resolveReportType(string $report): string
    {
        $value = strtolower(trim($report));
        if (! in_array($value, ['booking', 'collection', 'supplies'], true)) {
            return 'booking';
        }

        return $value;
    }

    /**
     * @return array{0:string,1:Carbon,2:Carbon,3:string,4:string,5:string}
     */
    private function resolveRange(Request $request): array
    {
        $period = strtolower((string) $request->query('period', $request->query('range', 'month')));
        $fromRaw = (string) $request->query('date_from', $request->query('from', ''));
        $toRaw = (string) $request->query('date_to', $request->query('to', ''));

        if ($period === 'custom') {
            $period = 'range';
        }

        if ($period === 'today') {
            $today = Carbon::today()->toDateString();
            $period = 'range';
            $fromRaw = $today;
            $toRaw = $today;
        }

        if (! in_array($period, ['week', 'month', 'range'], true)) {
            $period = 'month';
        }

        $today = Carbon::today();
        $fromParsed = $this->parseDate($fromRaw);
        $toParsed = $this->parseDate($toRaw);

        if ($period === 'week') {
            $start = $today->copy()->startOfWeek();
            $end = $today->copy()->endOfWeek();
            $label = 'This Week';
        } elseif ($period === 'range' && $fromParsed && $toParsed) {
            $start = $fromParsed->copy()->startOfDay();
            $end = $toParsed->copy()->endOfDay();
            if ($end->lt($start)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }
            $label = 'Custom Range';
        } else {
            $period = 'month';
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfMonth();
            $label = 'This Month';
        }

        return [
            $period,
            $start,
            $end,
            $start->toDateString(),
            $end->toDateString(),
            $label,
        ];
    }

    private function parseDate(string $value): ?Carbon
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $trimmed)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function buildReportPayload(string $report, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        return match ($report) {
            'collection' => $this->collectionReport($rangeStart, $rangeEnd),
            'supplies' => $this->suppliesReport($rangeStart, $rangeEnd),
            default => $this->bookingReport($rangeStart, $rangeEnd),
        };
    }

    /**
     * @return array<string,mixed>
     */
    private function bookingReport(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $events = AtriumEvent::query()
            ->with('functionHall:id,name,code')
            ->whereDate('date_of_event', '>=', $rangeStart->toDateString())
            ->whereDate('date_of_event', '<=', $rangeEnd->toDateString())
            ->orderByDesc('date_of_event')
            ->orderByDesc('id')
            ->get();

        $rows = $events->map(function (AtriumEvent $event): array {
            $eventDate = $event->date_of_event ? Carbon::parse($event->date_of_event) : null;
            $status = (string) ($event->booking_status ?? 'reserved');
            $amount = round((float) $event->actual_due, 2);

            return [
                'code' => (string) ($event->event_code ?: ('ATR-' . str_pad((string) $event->id, 6, '0', STR_PAD_LEFT))),
                'date' => $eventDate?->format('m/d/Y') ?? '-',
                'contact' => (string) ($event->name_contact_person ?? '-'),
                'hall' => (string) ($event->functionHall?->name ?? '-'),
                'hours' => round((float) $event->no_of_hours, 2),
                'amount' => $amount,
                'status' => $status,
                'status_label' => ucfirst($status),
                'status_class' => $this->statusClassForBooking($status),
                'week_key' => $eventDate?->copy()->startOfWeek()->toDateString() ?? 'n/a',
                'month_key' => $eventDate?->format('Y-m') ?? 'n/a',
            ];
        })->values();

        $statusCounts = [
            'reserved' => (int) $rows->where('status', 'reserved')->count(),
            'confirmed' => (int) $rows->where('status', 'confirmed')->count(),
            'completed' => (int) $rows->where('status', 'completed')->count(),
            'cancelled' => (int) $rows->where('status', 'cancelled')->count(),
        ];

        $weeklySummary = $this->buildWeeklySummary(
            $rows,
            fn (Collection $group): int => (int) $group->whereIn('status', ['confirmed', 'completed'])->count(),
            fn (Collection $group): int => (int) $group->where('status', 'cancelled')->count(),
            fn (Collection $group): float => (float) $group->sum('amount')
        );

        $monthlySummary = $this->buildMonthlySummary(
            $rows,
            fn (Collection $group): int => (int) $group->whereIn('status', ['confirmed', 'completed'])->count(),
            fn (Collection $group): int => (int) $group->where('status', 'cancelled')->count(),
            fn (Collection $group): float => (float) $group->sum('amount')
        );

        return [
            'reportTitle' => 'Atrium Booking Report',
            'reportDescription' => 'Track event bookings, hall usage, and billed totals.',
            'rows' => $rows,
            'totalRecords' => (int) $rows->count(),
            'primaryLabel' => 'Confirmed + Completed',
            'primaryCount' => $statusCounts['confirmed'] + $statusCounts['completed'],
            'secondaryLabel' => 'Cancelled',
            'secondaryCount' => $statusCounts['cancelled'],
            'metricLabel' => 'Total Billed',
            'metricIsCurrency' => true,
            'metricValue' => (float) $rows->sum('amount'),
            'summaryTotalLabel' => 'Total Due',
            'weeklySummary' => $weeklySummary,
            'monthlySummary' => $monthlySummary,
            'statusCounts' => $statusCounts,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function collectionReport(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $payments = AtriumEventPayment::query()
            ->with(['event.functionHall:id,name,code', 'recordedBy:id,name'])
            ->whereDate('date_of_payment', '>=', $rangeStart->toDateString())
            ->whereDate('date_of_payment', '<=', $rangeEnd->toDateString())
            ->orderByDesc('date_of_payment')
            ->orderByDesc('id')
            ->get();

        $rows = $payments->map(function (AtriumEventPayment $payment): array {
            $paidDate = $payment->date_of_payment ? Carbon::parse($payment->date_of_payment) : null;
            $status = (string) ($payment->payment_status ?? 'partial');

            return [
                'or_number' => (string) ($payment->or_number ?: ('OR-' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT))),
                'date' => $paidDate?->format('m/d/Y') ?? '-',
                'event' => (string) ($payment->event?->event_code ?? '-') . ' - ' . (string) ($payment->event?->name_contact_person ?? '-'),
                'amount' => round((float) $payment->payment_amount, 2),
                'status' => $status,
                'status_label' => ucfirst($status),
                'status_class' => $this->statusClassForCollection($status),
                'recorded_by' => (string) ($payment->recordedBy?->name ?? '-'),
                'week_key' => $paidDate?->copy()->startOfWeek()->toDateString() ?? 'n/a',
                'month_key' => $paidDate?->format('Y-m') ?? 'n/a',
            ];
        })->values();

        $statusCounts = [
            'paid' => (int) $rows->where('status', 'paid')->count(),
            'partial' => (int) $rows->where('status', 'partial')->count(),
            'unpaid' => (int) $rows->where('status', 'unpaid')->count(),
        ];

        $weeklySummary = $this->buildWeeklySummary(
            $rows,
            fn (Collection $group): int => (int) $group->where('status', 'paid')->count(),
            fn (Collection $group): int => (int) $group->whereIn('status', ['partial', 'unpaid'])->count(),
            fn (Collection $group): float => (float) $group->sum('amount')
        );

        $monthlySummary = $this->buildMonthlySummary(
            $rows,
            fn (Collection $group): int => (int) $group->where('status', 'paid')->count(),
            fn (Collection $group): int => (int) $group->whereIn('status', ['partial', 'unpaid'])->count(),
            fn (Collection $group): float => (float) $group->sum('amount')
        );

        return [
            'reportTitle' => 'Atrium Collection Report',
            'reportDescription' => 'Review official receipts and payment collection performance.',
            'rows' => $rows,
            'totalRecords' => (int) $rows->count(),
            'primaryLabel' => 'Paid',
            'primaryCount' => $statusCounts['paid'],
            'secondaryLabel' => 'Partial + Unpaid',
            'secondaryCount' => $statusCounts['partial'] + $statusCounts['unpaid'],
            'metricLabel' => 'Total Collected',
            'metricIsCurrency' => true,
            'metricValue' => (float) $rows->sum('amount'),
            'summaryTotalLabel' => 'Collected',
            'weeklySummary' => $weeklySummary,
            'monthlySummary' => $monthlySummary,
            'statusCounts' => $statusCounts,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function suppliesReport(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $orders = AtriumSuppliesOrder::query()
            ->with(['event.functionHall:id,name,code', 'requestedBy:id,name'])
            ->whereBetween('created_at', [$rangeStart->copy()->startOfDay(), $rangeEnd->copy()->endOfDay()])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $rows = $orders->map(function (AtriumSuppliesOrder $order): array {
            $createdAt = $order->created_at ? Carbon::parse($order->created_at) : null;
            $status = (string) ($order->request_status ?? 'pending');

            return [
                'date' => $createdAt?->format('m/d/Y') ?? '-',
                'event' => (string) ($order->event?->event_code ?? '-') . ' - ' . (string) ($order->event?->name_contact_person ?? '-'),
                'time_needed' => (string) ($order->time_needed ?? '-'),
                'supplies' => (string) ($order->requested_supplies ?? '-'),
                'status' => $status,
                'status_label' => ucfirst($status),
                'status_class' => $this->statusClassForSupply($status),
                'requested_by' => (string) ($order->requestedBy?->name ?? '-'),
                'week_key' => $createdAt?->copy()->startOfWeek()->toDateString() ?? 'n/a',
                'month_key' => $createdAt?->format('Y-m') ?? 'n/a',
            ];
        })->values();

        $statusCounts = [
            'pending' => (int) $rows->where('status', 'pending')->count(),
            'approved' => (int) $rows->where('status', 'approved')->count(),
            'fulfilled' => (int) $rows->where('status', 'fulfilled')->count(),
            'rejected' => (int) $rows->where('status', 'rejected')->count(),
        ];

        $weeklySummary = $this->buildWeeklySummary(
            $rows,
            fn (Collection $group): int => (int) $group->where('status', 'fulfilled')->count(),
            fn (Collection $group): int => (int) $group->whereIn('status', ['pending', 'approved'])->count(),
            fn (Collection $group): float => (float) $group->count()
        );

        $monthlySummary = $this->buildMonthlySummary(
            $rows,
            fn (Collection $group): int => (int) $group->where('status', 'fulfilled')->count(),
            fn (Collection $group): int => (int) $group->whereIn('status', ['pending', 'approved'])->count(),
            fn (Collection $group): float => (float) $group->count()
        );

        return [
            'reportTitle' => 'Atrium Supplies Report',
            'reportDescription' => 'Monitor supply requests and fulfillment workload.',
            'rows' => $rows,
            'totalRecords' => (int) $rows->count(),
            'primaryLabel' => 'Fulfilled',
            'primaryCount' => $statusCounts['fulfilled'],
            'secondaryLabel' => 'Pending + Approved',
            'secondaryCount' => $statusCounts['pending'] + $statusCounts['approved'],
            'metricLabel' => 'Total Requests',
            'metricIsCurrency' => false,
            'metricValue' => (float) $rows->count(),
            'summaryTotalLabel' => 'Requests',
            'weeklySummary' => $weeklySummary,
            'monthlySummary' => $monthlySummary,
            'statusCounts' => $statusCounts,
        ];
    }

    /**
     * @param callable(Collection):int $primaryCounter
     * @param callable(Collection):int $secondaryCounter
     * @param callable(Collection):float $totalResolver
     */
    private function buildWeeklySummary(Collection $rows, callable $primaryCounter, callable $secondaryCounter, callable $totalResolver): Collection
    {
        return $rows
            ->groupBy('week_key')
            ->map(function (Collection $group, string $weekKey) use ($primaryCounter, $secondaryCounter, $totalResolver): array {
                if ($weekKey === 'n/a') {
                    $label = 'No Date';
                } else {
                    $start = Carbon::parse($weekKey)->startOfWeek();
                    $end = $start->copy()->endOfWeek();
                    $label = $start->format('M d') . ' - ' . $end->format('M d, Y');
                }

                return [
                    'label' => $label,
                    'records' => (int) $group->count(),
                    'primary' => $primaryCounter($group),
                    'secondary' => $secondaryCounter($group),
                    'total' => $totalResolver($group),
                ];
            })
            ->values();
    }

    /**
     * @param callable(Collection):int $primaryCounter
     * @param callable(Collection):int $secondaryCounter
     * @param callable(Collection):float $totalResolver
     */
    private function buildMonthlySummary(Collection $rows, callable $primaryCounter, callable $secondaryCounter, callable $totalResolver): Collection
    {
        return $rows
            ->groupBy('month_key')
            ->map(function (Collection $group, string $monthKey) use ($primaryCounter, $secondaryCounter, $totalResolver): array {
                if ($monthKey === 'n/a') {
                    $label = 'No Date';
                } else {
                    $label = Carbon::parse($monthKey . '-01')->format('F Y');
                }

                return [
                    'label' => $label,
                    'records' => (int) $group->count(),
                    'primary' => $primaryCounter($group),
                    'secondary' => $secondaryCounter($group),
                    'total' => $totalResolver($group),
                ];
            })
            ->values();
    }

    private function statusClassForBooking(string $status): string
    {
        return match ($status) {
            'confirmed', 'completed' => 'ar-tag-good',
            'cancelled' => 'ar-tag-bad',
            default => 'ar-tag-warn',
        };
    }

    private function statusClassForCollection(string $status): string
    {
        return match ($status) {
            'paid' => 'ar-tag-good',
            'unpaid' => 'ar-tag-bad',
            default => 'ar-tag-warn',
        };
    }

    private function statusClassForSupply(string $status): string
    {
        return match ($status) {
            'fulfilled' => 'ar-tag-good',
            'rejected' => 'ar-tag-bad',
            default => 'ar-tag-warn',
        };
    }
}
