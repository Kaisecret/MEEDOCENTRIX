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

    public function csv(Request $request)
    {
        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($rangeStart, $rangeEnd);
        $filename = 'fishport-report-' . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.xls';

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
    <title>Fishport Transactions Report</title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Fishport Report</x:Name>
                    <x:WorksheetOptions>
                        <x:DisplayGridlines/>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style><?= $css ?></style>
</head>
<body>

<table>
    <tr><td colspan="11" class="title">Fishport Management Office</td></tr>
    <tr><td colspan="11" class="subtitle">Transactions &amp; Collection Report</td></tr>
    <tr><td colspan="11" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="11">&nbsp;</td></tr>
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
            <td class="kpi-paid int center">
                <?= (int) $payload['paidTransactions'] ?>
                &nbsp;(<?= $money($payload['paidAmount']) ?>)
            </td>
            <td class="kpi-unpaid int center">
                <?= (int) $payload['notPaidTransactions'] ?>
                &nbsp;(<?= $money($payload['notPaidAmount']) ?>)
            </td>
            <td class="num center" style="font-weight:bold;color:#0c3a5b;">
                <?= $money($payload['totalAmount']) ?>
            </td>
        </tr>
    </tbody>
</table>

<br>

<?php if ($period === 'week'): ?>
<table>
    <tr><td colspan="5" class="section-title">WEEKLY SUMMARY</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th>Week</th>
            <th class="int">Transactions</th>
            <th class="int">Paid</th>
            <th class="int">Not Paid</th>
            <th class="num">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($payload['weeklySummary'] as $row): $i++; ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($row['label']) ?></strong></td>
                <td class="int"><?= number_format((int) $row['transactions']) ?></td>
                <td class="int paid"><?= number_format((int) $row['paid']) ?></td>
                <td class="int unpaid"><?= number_format((int) $row['not_paid']) ?></td>
                <td class="num"><?= $money($row['total']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($payload['weeklySummary']->isEmpty()): ?>
            <tr><td colspan="5" class="center" style="color:#94a3b8;font-style:italic;">No weekly records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<br>
<?php endif; ?>

<?php if ($period === 'month'): ?>
<table>
    <tr><td colspan="5" class="section-title">MONTHLY SUMMARY</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th>Month</th>
            <th class="int">Transactions</th>
            <th class="int">Paid</th>
            <th class="int">Not Paid</th>
            <th class="num">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($payload['monthlySummary'] as $row): $i++; ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($row['label']) ?></strong></td>
                <td class="int"><?= number_format((int) $row['transactions']) ?></td>
                <td class="int paid"><?= number_format((int) $row['paid']) ?></td>
                <td class="int unpaid"><?= number_format((int) $row['not_paid']) ?></td>
                <td class="num"><?= $money($row['total']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($payload['monthlySummary']->isEmpty()): ?>
            <tr><td colspan="5" class="center" style="color:#94a3b8;font-style:italic;">No monthly records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<br>
<?php endif; ?>

<table>
    <tr><td colspan="11" class="section-title">DETAILED TRANSACTIONS</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th>Log ID</th>
            <th>Payment No.</th>
            <th>Date</th>
            <th>Time</th>
            <th>Vessel</th>
            <th class="center">ARR/DEP</th>
            <th>Origin</th>
            <th class="center">Status</th>
            <th class="num">Total Amount</th>
            <th>Payer</th>
            <th>Encoder</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($payload['transactions'] as $row): $i++; ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($row['log_id']) ?></strong></td>
                <td><?= $esc($row['payment_no']) ?></td>
                <td><?= $esc($row['date']) ?></td>
                <td><?= $esc($row['time']) ?></td>
                <td><?= $esc($row['vessel']) ?></td>
                <td class="center"><?= $esc($row['arr_dep']) ?></td>
                <td><?= $esc($row['origin']) ?></td>
                <td class="center <?= $row['is_paid'] ? 'paid' : 'unpaid' ?>"><?= $esc($row['status']) ?></td>
                <td class="num"><?= $money($row['total']) ?></td>
                <td><?= $esc($row['payer_name']) ?></td>
                <td><?= $esc($row['encoder']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($payload['transactions']->isEmpty()): ?>
            <tr><td colspan="11" class="center" style="color:#94a3b8;font-style:italic;">No transactions found in the selected range.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<table>
    <tr><td colspan="11" class="footer">Fishport Management System &middot; Confidential Report &middot; Generated <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
</table>

</body>
</html>
        <?php
        return (string) ob_get_clean();
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
