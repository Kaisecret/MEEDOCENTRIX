<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\CollectionDispatchItem;
use App\Models\CollectorDepartmentAssignment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CollectorReportController extends Controller
{
    private const PDF_MAX_ROWS = 100;

    public function index(Request $request): View
    {
        $assignment = $this->collectorAssignment($request);
        $departmentCode = $assignment?->department?->code;

        if (! $departmentCode) {
            return view('collector.reports', $this->emptyReportPayload('No Assignment'));
        }

        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($request, $rangeStart, $rangeEnd, $departmentCode);

        $viewName = $departmentCode === 'market' ? 'collector.reports_market' : 'collector.reports';

        return view($viewName, [
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rangeLabel' => $rangeLabel,
            ...$payload,
        ]);
    }

    public function preview(Request $request): View
    {
        $assignment = $this->collectorAssignment($request);
        $departmentCode = $assignment?->department?->code;

        if (! $departmentCode) {
            return view('collector.reports_pdf', [
                ...$this->emptyReportPayload('No Assignment'),
                'generatedAt' => now(),
            ]);
        }

        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($request, $rangeStart, $rangeEnd, $departmentCode);
        $previewRows = $payload['transactions']->take(self::PDF_MAX_ROWS)->values();

        $viewName = $departmentCode === 'market' ? 'collector.reports_pdf_market' : 'collector.reports_pdf';

        return view($viewName, [
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rangeLabel' => $rangeLabel,
            'generatedAt' => now(),
            ...$payload,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
            'pdfDisplayedRows' => $previewRows->count(),
            'pdfTotalRows' => (int) $payload['transactions']->count(),
            'transactions' => $previewRows,
        ]);
    }

    public function pdf(Request $request)
    {
        $assignment = $this->collectorAssignment($request);
        $departmentCode = $assignment?->department?->code;

        if (! $departmentCode) {
            return Pdf::loadView('collector.reports_pdf', [
                ...$this->emptyReportPayload('No Assignment'),
                'generatedAt' => now(),
            ])->download('collector-report.pdf');
        }

        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($request, $rangeStart, $rangeEnd, $departmentCode);
        $pdfRows = $payload['transactions']->take(self::PDF_MAX_ROWS)->values();

        $prefix = $departmentCode === 'market' ? 'market-collector-report-' : 'collector-report-';
        $filename = $prefix . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.pdf';
        $viewName = $departmentCode === 'market' ? 'collector.reports_pdf_market' : 'collector.reports_pdf';

        return Pdf::loadView($viewName, [
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rangeLabel' => $rangeLabel,
            'generatedAt' => now(),
            ...$payload,
            'pdfMaxRows' => self::PDF_MAX_ROWS,
            'pdfDisplayedRows' => $pdfRows->count(),
            'pdfTotalRows' => (int) $payload['transactions']->count(),
            'transactions' => $pdfRows,
        ])->download($filename);
    }

    /**
     * @return array{0:string,1:Carbon,2:Carbon,3:string,4:string,5:string}
     */
    private function resolveRange(Request $request): array
    {
        $period = strtolower((string) $request->query('period', 'month'));
        if (! in_array($period, ['day', 'week', 'month', 'range'], true)) {
            $period = 'month';
        }

        $today = Carbon::today();
        $fromParsed = $this->parseDate((string) $request->query('date_from', ''));
        $toParsed = $this->parseDate((string) $request->query('date_to', ''));

        if ($period === 'day') {
            $start = $today->copy()->startOfDay();
            $end = $today->copy()->endOfDay();
            $label = 'Today';
        } elseif ($period === 'week') {
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
    private function buildReportPayload(Request $request, Carbon $rangeStart, Carbon $rangeEnd, string $departmentCode = ''): array
    {
        if ($departmentCode === 'market') {
            $items = CollectionDispatchItem::query()
                ->with([
                    'dispatch:id,collector_user_id,department_code,created_at',
                    'marketStallLease:id,market_stall_id,market_tenant_id',
                    'marketStallLease.stall:id,stall_no,market_stall_location_id',
                    'marketStallLease.stall.location:id,location_code,location_name',
                    'marketStallLease.tenant:id,first_name,middle_name,last_name,business_name',
                    'marketPaymentCollection:id,payment_number',
                    'collectedBy:id,name',
                ])
                ->whereHas('dispatch', static function ($query) use ($request, $departmentCode): void {
                    $query->where('collector_user_id', (int) $request->user()?->id);
                    $query->where('department_code', $departmentCode);
                })
                ->whereDate('created_at', '>=', $rangeStart->toDateString())
                ->whereDate('created_at', '<=', $rangeEnd->toDateString())
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            $transactions = $items->map(function (CollectionDispatchItem $item): array {
                $lease = $item->marketStallLease;
                $stall = $lease?->stall;
                $tenant = $lease?->tenant;
                $createdAt = $item->created_at;

                return [
                    'stall_no' => (string) ($stall?->stall_no ?: '-'),
                    'location' => (string) ($stall?->location?->location_code ?: ($stall?->location?->location_name ?: '-')),
                    'tenant_name' => (string) ($tenant ? $tenant->fullName() : '-'),
                    'business_name' => (string) ($tenant?->business_name ?: '-'),
                    'payment_no' => (string) ($item->marketPaymentCollection?->payment_number ?? '-'),
                    'date' => $createdAt?->format('m/d/Y') ?? '-',
                    'time' => $createdAt?->format('h:i A') ?? '-',
                    'collector' => (string) ($item->collectedBy?->name ?? '-'),
                    'payer_name' => (string) ($item->payer_name ?? '-'),
                    'status' => $this->statusLabel((string) $item->status),
                    'status_key' => (string) $item->status,
                    'amount' => round((float) $item->amount_snapshot, 2),
                    'week_key' => $createdAt?->copy()->startOfWeek()->toDateString() ?? 'n/a',
                    'month_key' => $createdAt?->format('Y-m') ?? 'n/a',
                ];
            })->values();
        } else {
            $items = CollectionDispatchItem::query()
                ->with([
                    'dispatch:id,collector_user_id,department_code,created_at',
                    'fishportLog:id,log_number,log_date,log_time,arr_dep,fishport_vessel_id,fishport_origin_id',
                    'fishportLog.vessel:id,name',
                    'fishportLog.origin:id,name',
                    'paymentRecord:id,fishport_log_id,payment_number',
                    'collectedBy:id,name',
                ])
                ->whereHas('dispatch', static function ($query) use ($request, $departmentCode): void {
                    $query->where('collector_user_id', (int) $request->user()?->id);
                    if ($departmentCode !== '') {
                        $query->where('department_code', $departmentCode);
                    }
                })
                ->whereDate('created_at', '>=', $rangeStart->toDateString())
                ->whereDate('created_at', '<=', $rangeEnd->toDateString())
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            $transactions = $items->map(function (CollectionDispatchItem $item): array {
                $log = $item->fishportLog;
                $logDate = $log?->log_date ? Carbon::parse($log->log_date) : null;
                $logTime = trim((string) $log?->log_time);

                return [
                    'log_id' => (string) ($log?->log_number ?: ($log ? ('FP-' . str_pad((string) $log->id, 6, '0', STR_PAD_LEFT)) : '-')),
                    'payment_no' => (string) ($item->paymentRecord?->payment_number ?? '-'),
                    'vessel' => (string) ($log?->vessel?->name ?? '-'),
                    'arr_dep' => (string) ($log?->arr_dep ?? '-'),
                    'origin' => (string) ($log?->origin?->name ?? '-'),
                    'date' => $logDate?->format('m/d/Y') ?? '-',
                    'time' => $logTime !== '' ? substr($logTime, 0, 5) : '-',
                    'collector' => (string) ($item->collectedBy?->name ?? '-'),
                    'payer_name' => (string) ($item->payer_name ?? '-'),
                    'status' => $this->statusLabel((string) $item->status),
                    'status_key' => (string) $item->status,
                    'amount' => round((float) $item->amount_snapshot, 2),
                    'week_key' => $item->created_at?->copy()->startOfWeek()->toDateString() ?? 'n/a',
                    'month_key' => $item->created_at?->format('Y-m') ?? 'n/a',
                ];
            })->values();
        }

        $totalTransactions = $transactions->count();
        $pendingTransactions = $transactions->whereIn('status_key', ['sent', 'rejected'])->count();
        $awaitingTransactions = $transactions->where('status_key', 'collected_pending_confirmation')->count();
        $acceptedTransactions = $transactions->where('status_key', 'accepted')->count();
        $totalAmount = (float) $transactions->sum('amount');
        $acceptedAmount = (float) $transactions->where('status_key', 'accepted')->sum('amount');
        $pendingAmount = (float) $transactions->whereIn('status_key', ['sent', 'rejected'])->sum('amount');

        $weeklySummary = $transactions
            ->groupBy('week_key')
            ->map(function ($rows, string $weekKey): array {
                if ($weekKey === 'n/a') {
                    return [
                        'label' => 'No Date',
                        'transactions' => (int) $rows->count(),
                        'accepted' => (int) $rows->where('status_key', 'accepted')->count(),
                        'pending' => (int) $rows->whereIn('status_key', ['sent', 'rejected'])->count(),
                        'total' => (float) $rows->sum('amount'),
                    ];
                }

                $start = Carbon::parse($weekKey)->startOfWeek();
                $end = $start->copy()->endOfWeek();

                return [
                    'label' => $start->format('M d') . ' - ' . $end->format('M d, Y'),
                    'transactions' => (int) $rows->count(),
                    'accepted' => (int) $rows->where('status_key', 'accepted')->count(),
                    'pending' => (int) $rows->whereIn('status_key', ['sent', 'rejected'])->count(),
                    'total' => (float) $rows->sum('amount'),
                ];
            })
            ->values();

        $monthlySummary = $transactions
            ->groupBy('month_key')
            ->map(function ($rows, string $monthKey): array {
                if ($monthKey === 'n/a') {
                    return [
                        'label' => 'No Date',
                        'transactions' => (int) $rows->count(),
                        'accepted' => (int) $rows->where('status_key', 'accepted')->count(),
                        'pending' => (int) $rows->whereIn('status_key', ['sent', 'rejected'])->count(),
                        'total' => (float) $rows->sum('amount'),
                    ];
                }

                return [
                    'label' => Carbon::parse($monthKey . '-01')->format('F Y'),
                    'transactions' => (int) $rows->count(),
                    'accepted' => (int) $rows->where('status_key', 'accepted')->count(),
                    'pending' => (int) $rows->whereIn('status_key', ['sent', 'rejected'])->count(),
                    'total' => (float) $rows->sum('amount'),
                ];
            })
            ->values();

        return [
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'transactions' => $transactions,
            'totalTransactions' => $totalTransactions,
            'pendingTransactions' => $pendingTransactions,
            'awaitingTransactions' => $awaitingTransactions,
            'acceptedTransactions' => $acceptedTransactions,
            'totalAmount' => $totalAmount,
            'acceptedAmount' => $acceptedAmount,
            'pendingAmount' => $pendingAmount,
            'weeklySummary' => $weeklySummary,
            'monthlySummary' => $monthlySummary,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function emptyReportPayload(string $rangeLabel): array
    {
        $today = Carbon::today();

        return [
            'period' => 'month',
            'dateFrom' => $today->copy()->startOfMonth()->toDateString(),
            'dateTo' => $today->copy()->endOfMonth()->toDateString(),
            'rangeLabel' => $rangeLabel,
            'rangeStart' => $today->copy()->startOfMonth(),
            'rangeEnd' => $today->copy()->endOfMonth(),
            'transactions' => collect(),
            'totalTransactions' => 0,
            'pendingTransactions' => 0,
            'awaitingTransactions' => 0,
            'acceptedTransactions' => 0,
            'totalAmount' => 0.0,
            'acceptedAmount' => 0.0,
            'pendingAmount' => 0.0,
            'weeklySummary' => collect(),
            'monthlySummary' => collect(),
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'sent' => 'Pending',
            'rejected' => 'Rejected',
            'collected_pending_confirmation' => 'Awaiting Confirmation',
            'accepted' => 'Accepted',
            default => 'Unknown',
        };
    }

    private function collectorAssignment(Request $request): ?CollectorDepartmentAssignment
    {
        return CollectorDepartmentAssignment::query()
            ->with('department:id,code,name')
            ->where('collector_user_id', (int) $request->user()?->id)
            ->first();
    }
}
