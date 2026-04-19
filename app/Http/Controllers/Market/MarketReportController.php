<?php

namespace App\Http\Controllers\Market;

use App\Http\Controllers\Controller;
use App\Models\CollectionDispatchItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MarketReportController extends Controller
{
    private const PDF_MAX_ROWS = 100;

    public function index(Request $request): View
    {
        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($rangeStart, $rangeEnd);

        return view('market.reports', [
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rangeLabel' => $rangeLabel,
            ...$payload,
        ]);
    }

    public function preview(Request $request): View
    {
        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($rangeStart, $rangeEnd);
        $previewRows = $payload['transactions']->take(self::PDF_MAX_ROWS)->values();

        return view('market.reports_pdf', [
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
        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($rangeStart, $rangeEnd);
        $pdfRows = $payload['transactions']->take(self::PDF_MAX_ROWS)->values();

        $filename = 'market-report-' . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.pdf';

        return Pdf::loadView('market.reports_pdf', [
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
        if (! in_array($period, ['week', 'month', 'range'], true)) {
            $period = 'month';
        }

        $today = Carbon::today();
        $fromParsed = $this->parseDate((string) $request->query('date_from', ''));
        $toParsed = $this->parseDate((string) $request->query('date_to', ''));

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
    private function buildReportPayload(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $items = CollectionDispatchItem::query()
            ->with([
                'dispatch.collector:id,name',
                'marketStallLease:id,contract_number,market_stall_id,market_tenant_id',
                'marketStallLease.stall:id,stall_no,market_stall_location_id',
                'marketStallLease.stall.location:id,location_code,location_name',
                'marketStallLease.tenant:id,first_name,middle_name,last_name,business_name',
                'marketPaymentCollection:id,payment_number,payment_date',
                'collectedBy:id,name',
            ])
            ->whereHas('dispatch', static function ($query): void {
                $query->where('department_code', 'market');
            })
            ->whereDate('updated_at', '>=', $rangeStart->toDateString())
            ->whereDate('updated_at', '<=', $rangeEnd->toDateString())
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $transactions = $items->map(function (CollectionDispatchItem $item): array {
            $lease = $item->marketStallLease;
            $stall = $lease?->stall;
            $tenant = $lease?->tenant;
            $updatedAt = $item->updated_at;

            return [
                'stall_no' => (string) ($stall?->stall_no ?: '-'),
                'location' => (string) ($stall?->location?->location_code ?: ($stall?->location?->location_name ?: '-')),
                'tenant_name' => (string) ($tenant ? $tenant->fullName() : '-'),
                'business_name' => (string) ($tenant?->business_name ?: '-'),
                'contract_no' => (string) ($lease?->contract_number ?: '-'),
                'payment_no' => (string) ($item->marketPaymentCollection?->payment_number ?? '-'),
                'date' => $updatedAt?->format('m/d/Y') ?? '-',
                'time' => $updatedAt?->format('h:i A') ?? '-',
                'collector' => (string) ($item->collectedBy?->name ?? $item->dispatch?->collector?->name ?? '-'),
                'payer_name' => (string) ($item->payer_name ?? '-'),
                'status' => $this->statusLabel((string) $item->status),
                'status_key' => (string) $item->status,
                'amount' => round((float) $item->amount_snapshot, 2),
                'week_key' => $updatedAt?->copy()->startOfWeek()->toDateString() ?? 'n/a',
                'month_key' => $updatedAt?->format('Y-m') ?? 'n/a',
            ];
        })->values();

        $totalTransactions = $transactions->count();
        $pendingTransactions = $transactions->whereIn('status_key', ['sent', 'rejected'])->count();
        $awaitingTransactions = $transactions->where('status_key', 'collected_pending_confirmation')->count();
        $acceptedTransactions = $transactions->where('status_key', 'accepted')->count();
        $cancelledTransactions = $transactions->where('status_key', 'cancelled')->count();
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
                        'awaiting' => (int) $rows->where('status_key', 'collected_pending_confirmation')->count(),
                        'cancelled' => (int) $rows->where('status_key', 'cancelled')->count(),
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
                    'awaiting' => (int) $rows->where('status_key', 'collected_pending_confirmation')->count(),
                    'cancelled' => (int) $rows->where('status_key', 'cancelled')->count(),
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
                        'awaiting' => (int) $rows->where('status_key', 'collected_pending_confirmation')->count(),
                        'cancelled' => (int) $rows->where('status_key', 'cancelled')->count(),
                        'total' => (float) $rows->sum('amount'),
                    ];
                }

                return [
                    'label' => Carbon::parse($monthKey . '-01')->format('F Y'),
                    'transactions' => (int) $rows->count(),
                    'accepted' => (int) $rows->where('status_key', 'accepted')->count(),
                    'pending' => (int) $rows->whereIn('status_key', ['sent', 'rejected'])->count(),
                    'awaiting' => (int) $rows->where('status_key', 'collected_pending_confirmation')->count(),
                    'cancelled' => (int) $rows->where('status_key', 'cancelled')->count(),
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
            'cancelledTransactions' => $cancelledTransactions,
            'totalAmount' => $totalAmount,
            'acceptedAmount' => $acceptedAmount,
            'pendingAmount' => $pendingAmount,
            'weeklySummary' => $weeklySummary,
            'monthlySummary' => $monthlySummary,
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'sent' => 'Pending',
            'rejected' => 'Rejected',
            'collected_pending_confirmation' => 'Awaiting Confirmation',
            'accepted' => 'Accepted',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }
}
