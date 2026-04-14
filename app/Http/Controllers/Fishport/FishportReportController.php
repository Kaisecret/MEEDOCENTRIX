<?php

namespace App\Http\Controllers\Fishport;

use App\Http\Controllers\Controller;
use App\Models\FishportLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FishportReportController extends Controller
{
    private const PDF_MAX_ROWS = 100;

    public function index(Request $request): View
    {
        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($rangeStart, $rangeEnd);

        return view('fishport.reports', [
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

        return view('fishport.reports_pdf', [
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

        $filename = 'fishport-report-' . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.pdf';

        return Pdf::loadView('fishport.reports_pdf', [
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
        $logs = FishportLog::query()
            ->with([
                'vessel:id,name',
                'origin:id,name',
                'user:id,name',
                'paymentRecord:id,fishport_log_id,payment_number,total_amount,payer_name',
            ])
            ->withSum('payments', 'total')
            ->whereDate('log_date', '>=', $rangeStart->toDateString())
            ->whereDate('log_date', '<=', $rangeEnd->toDateString())
            ->orderByDesc('log_date')
            ->orderByDesc('log_time')
            ->orderByDesc('id')
            ->get();

        $transactions = $logs->map(function (FishportLog $log): array {
            $amount = $this->resolveAmount($log);
            $logDate = $log->log_date ? Carbon::parse($log->log_date) : null;
            $logTime = trim((string) $log->log_time);

            return [
                'log_id' => $log->log_number ?: ('FP-' . str_pad((string) $log->id, 6, '0', STR_PAD_LEFT)),
                'payment_no' => (string) ($log->paymentRecord?->payment_number ?? '-'),
                'vessel' => (string) ($log->vessel?->name ?? '-'),
                'arr_dep' => (string) ($log->arr_dep ?: '-'),
                'origin' => (string) ($log->origin?->name ?? '-'),
                'date' => $logDate?->format('m/d/Y') ?? '-',
                'time' => $logTime !== '' ? substr($logTime, 0, 5) : '-',
                'encoder' => (string) ($log->user?->name ?? 'Unknown'),
                'payer_name' => (string) ($log->paymentRecord?->payer_name ?? '-'),
                'is_paid' => (bool) $log->is_paid,
                'status' => $log->is_paid ? 'Paid' : 'Not Paid',
                'total' => $amount,
                'week_key' => $logDate?->copy()->startOfWeek()->toDateString() ?? 'n/a',
                'month_key' => $logDate?->format('Y-m') ?? 'n/a',
            ];
        })->values();

        $totalTransactions = $transactions->count();
        $paidTransactions = $transactions->where('is_paid', true)->count();
        $notPaidTransactions = $transactions->where('is_paid', false)->count();
        $totalAmount = (float) $transactions->sum('total');
        $paidAmount = (float) $transactions->where('is_paid', true)->sum('total');
        $notPaidAmount = (float) $transactions->where('is_paid', false)->sum('total');

        $weeklySummary = $transactions
            ->groupBy('week_key')
            ->map(function ($rows, string $weekKey): array {
                if ($weekKey === 'n/a') {
                    return [
                        'label' => 'No Date',
                        'transactions' => (int) $rows->count(),
                        'paid' => (int) $rows->where('is_paid', true)->count(),
                        'not_paid' => (int) $rows->where('is_paid', false)->count(),
                        'total' => (float) $rows->sum('total'),
                    ];
                }

                $start = Carbon::parse($weekKey)->startOfWeek();
                $end = $start->copy()->endOfWeek();

                return [
                    'label' => $start->format('M d') . ' - ' . $end->format('M d, Y'),
                    'transactions' => (int) $rows->count(),
                    'paid' => (int) $rows->where('is_paid', true)->count(),
                    'not_paid' => (int) $rows->where('is_paid', false)->count(),
                    'total' => (float) $rows->sum('total'),
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
                        'paid' => (int) $rows->where('is_paid', true)->count(),
                        'not_paid' => (int) $rows->where('is_paid', false)->count(),
                        'total' => (float) $rows->sum('total'),
                    ];
                }

                return [
                    'label' => Carbon::parse($monthKey . '-01')->format('F Y'),
                    'transactions' => (int) $rows->count(),
                    'paid' => (int) $rows->where('is_paid', true)->count(),
                    'not_paid' => (int) $rows->where('is_paid', false)->count(),
                    'total' => (float) $rows->sum('total'),
                ];
            })
            ->values();

        return [
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'transactions' => $transactions,
            'totalTransactions' => $totalTransactions,
            'paidTransactions' => $paidTransactions,
            'notPaidTransactions' => $notPaidTransactions,
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount,
            'notPaidAmount' => $notPaidAmount,
            'weeklySummary' => $weeklySummary,
            'monthlySummary' => $monthlySummary,
        ];
    }

    private function resolveAmount(FishportLog $log): float
    {
        $paymentsTotal = (float) ($log->payments_sum_total ?? 0);
        if ($paymentsTotal > 0) {
            return round($paymentsTotal, 2);
        }

        return round((float) ($log->paymentRecord?->total_amount ?? 0), 2);
    }
}
