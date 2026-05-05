<?php

namespace App\Http\Controllers\Terminal;

use App\Http\Controllers\Controller;
use App\Models\TerminalQuickPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TerminalReportController extends Controller
{
    private const PDF_MAX_ROWS = 120;

    public function index(Request $request): View
    {
        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($rangeStart, $rangeEnd);

        return view('terminal.reports', [
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

        return view('terminal.reports_pdf', [
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

        $filename = 'terminal-report-' . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.pdf';

        return Pdf::loadView('terminal.reports_pdf', [
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

    public function csv(Request $request)
    {
        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($rangeStart, $rangeEnd);
        $filename = 'terminal-report-' . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->streamDownload(function () use ($payload, $period, $rangeStart, $rangeEnd, $rangeLabel): void {
            echo "\xEF\xBB\xBF";
            echo $this->renderExcelHtml($payload, $period, $rangeStart, $rangeEnd, $rangeLabel);
        }, $filename, $headers);
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
            $label = 'This Day';
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
    private function buildReportPayload(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $payments = TerminalQuickPayment::query()
            ->with(['recordedBy:id,name', 'paidBy:id,name'])
            ->whereDate('payment_date', '>=', $rangeStart->toDateString())
            ->whereDate('payment_date', '<=', $rangeEnd->toDateString())
            ->whereNotNull('ticket_number')
            ->where('ticket_number', '<>', '')
            ->whereNotNull('route_code')
            ->where('route_code', '<>', '')
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        $transactions = $payments->map(static function (TerminalQuickPayment $payment): array {
            $recordedAt = $payment->payment_date ? Carbon::parse($payment->payment_date) : null;
            $paidAt = $payment->paid_at ? Carbon::parse($payment->paid_at) : null;

            return [
                'ticket_number' => (string) ($payment->ticket_number ?? '-'),
                'vehicle' => (string) ($payment->vehicle_kind ?? '-'),
                'route' => (string) ($payment->route_name ?? '-'),
                'recorded_date' => $recordedAt?->format('m/d/Y') ?? '-',
                'recorded_time' => $recordedAt?->format('h:i A') ?? '-',
                'paid_at' => $paidAt?->format('m/d/Y h:i A') ?? '-',
                'recorded_by' => (string) ($payment->recordedBy?->name ?? 'Unknown'),
                'paid_by' => (string) ($payment->paidBy?->name ?? '-'),
                'payer_name' => (string) ($payment->payer_name ?: '-'),
                'remarks' => (string) ($payment->remarks ?: '-'),
                'is_paid' => (bool) $payment->is_paid,
                'status' => $payment->is_paid ? 'Paid' : 'Not Paid',
                'total' => (float) $payment->total_payment,
                'week_key' => $recordedAt?->copy()->startOfWeek()->toDateString() ?? 'n/a',
                'month_key' => $recordedAt?->format('Y-m') ?? 'n/a',
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

    private function renderExcelHtml(array $payload, string $period, Carbon $rangeStart, Carbon $rangeEnd, string $rangeLabel): string
    {
        $esc = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $money = static fn ($v): string => 'PHP ' . number_format((float) $v, 2);

        $css = '
            body { font-family: Calibri, "Segoe UI", Arial, sans-serif; color:#0f172a; }
            table { border-collapse: collapse; width: 100%; }
            .title { font-size:18pt; font-weight:bold; color:#0c3a5b; }
            .subtitle { font-size:11pt; color:#475569; }
            .meta { font-size:10pt; color:#475569; }
            .section-title {
                background:#0c3a5b; color:#ffffff; font-weight:bold;
                padding:6pt 10pt; font-size:11pt; letter-spacing:1pt;
            }
            .info th {
                background:#eaf2f9; color:#0c3a5b; text-align:left;
                font-weight:bold; padding:6pt 10pt; border:1px solid #cbd5e1;
            }
            .info td {
                background:#f8fafc; padding:6pt 10pt;
                border:1px solid #cbd5e1; font-weight:bold;
            }
            .data th {
                background:#155f8f; color:#ffffff; font-weight:bold;
                padding:6pt 8pt; border:1px solid #0c3a5b;
                text-align:left; font-size:10pt;
            }
            .data td {
                padding:5pt 8pt; border:1px solid #cbd5e1; font-size:10pt;
                vertical-align:top;
            }
            .data tr.alt td { background:#f8fafc; }
            .num { mso-number-format:"#,##0.00"; text-align:right; }
            .int { mso-number-format:"#,##0"; text-align:right; }
            .center { text-align:center; }
            .paid { color:#047857; font-weight:bold; }
            .unpaid { color:#b91c1c; font-weight:bold; }
            .kpi-paid { background:#ecfdf5; color:#047857; font-weight:bold; }
            .kpi-unpaid { background:#fef2f2; color:#b91c1c; font-weight:bold; }
            .kpi-total { background:#fffbeb; color:#b45309; font-weight:bold; }
            .footer { font-size:9pt; color:#64748b; font-style:italic; padding-top:8pt; }
        ';

        ob_start();
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Terminal Transactions Report</title>
    <style><?= $css ?></style>
</head>
<body>
<table>
    <tr><td colspan="10" class="title">Terminal Fee Collection Office</td></tr>
    <tr><td colspan="10" class="subtitle">Transactions &amp; Collection Report</td></tr>
    <tr><td colspan="10" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="10">&nbsp;</td></tr>
</table>

<table class="info">
    <tr>
        <th style="width:18%;">Period</th>
        <td style="width:32%;"><?= $esc($rangeLabel) ?></td>
        <th style="width:18%;">Total Records</th>
        <td style="width:32%;"><?= number_format((int) $payload['totalTransactions']) ?> transaction(s)</td>
    </tr>
    <tr>
        <th>Date From</th>
        <td><?= $esc($rangeStart->format('F d, Y')) ?></td>
        <th>Date To</th>
        <td><?= $esc($rangeEnd->format('F d, Y')) ?></td>
    </tr>
</table>

<br>
<table>
    <tr><td colspan="4" class="section-title">SUMMARY</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th style="width:25%;">Total Transactions</th>
            <th style="width:25%;">Paid Transactions</th>
            <th style="width:25%;">Not Paid Transactions</th>
            <th style="width:25%;">Total Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="kpi-total int center"><?= (int) $payload['totalTransactions'] ?></td>
            <td class="kpi-paid int center"><?= (int) $payload['paidTransactions'] ?>&nbsp;(<?= $money($payload['paidAmount']) ?>)</td>
            <td class="kpi-unpaid int center"><?= (int) $payload['notPaidTransactions'] ?>&nbsp;(<?= $money($payload['notPaidAmount']) ?>)</td>
            <td class="num center" style="font-weight:bold;color:#0c3a5b;"><?= $money($payload['totalAmount']) ?></td>
        </tr>
    </tbody>
</table>

<br>
<table>
    <tr><td colspan="10" class="section-title">DETAILED TRANSACTIONS</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th>Ticket #</th>
            <th>Vehicle</th>
            <th>Route / Operator</th>
            <th>Date</th>
            <th>Time</th>
            <th class="center">Status</th>
            <th class="num">Amount</th>
            <th>Remarks</th>
            <th>Saved By</th>
            <th>Paid By</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($payload['transactions'] as $row): $i++; ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($row['ticket_number']) ?></strong></td>
                <td><?= $esc($row['vehicle']) ?></td>
                <td><?= $esc($row['route']) ?></td>
                <td><?= $esc($row['recorded_date']) ?></td>
                <td><?= $esc($row['recorded_time']) ?></td>
                <td class="center <?= $row['is_paid'] ? 'paid' : 'unpaid' ?>"><?= $esc($row['status']) ?></td>
                <td class="num"><?= $money($row['total']) ?></td>
                <td><?= $esc($row['remarks']) ?></td>
                <td><?= $esc($row['recorded_by']) ?></td>
                <td><?= $esc($row['paid_by']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($payload['transactions']->isEmpty()): ?>
            <tr><td colspan="10" class="center" style="color:#94a3b8;font-style:italic;">No transactions found in the selected range.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<table>
    <tr><td colspan="10" class="footer">Terminal Management System &middot; Confidential Report &middot; Generated <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
</table>

</body>
</html>
        <?php

        return (string) ob_get_clean();
    }
}
