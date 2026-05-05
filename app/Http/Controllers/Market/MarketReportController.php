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

    public function csv(Request $request)
    {
        [$period, $rangeStart, $rangeEnd, , , $rangeLabel] = $this->resolveRange($request);
        $payload = $this->buildReportPayload($rangeStart, $rangeEnd);
        $filename = 'market-report-' . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.xls';

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
                'record_id' => 'MKT-CN-' . now()->format('Y') . '-' . str_pad((string) $item->id, 4, '0', STR_PAD_LEFT),
                'stall_no' => (string) ($stall?->stall_no ?: '-'),
                'location' => (string) ($stall?->location?->location_code ?: ($stall?->location?->location_name ?: '-')),
                'tenant_name' => (string) ($tenant ? $tenant->fullName() : '-'),
                'business_name' => (string) ($tenant?->business_name ?: '-'),
                'contract_no' => (string) ($lease?->contract_number ?: '-'),
                'payment_no' => (string) ($item->marketPaymentCollection?->payment_number ?? '-'),
                'date' => $updatedAt?->format('m/d/Y') ?? '-',
                'time' => $updatedAt?->format('h:i A') ?? '-',
                'collector' => (string) ($item->collectedBy?->name ?? $item->dispatch?->collector?->name ?? '-'),
                'encoder' => (string) ($item->dispatch?->collector?->name ?? $item->collectedBy?->name ?? '-'),
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

    private function renderExcelHtml(array $payload, string $period, Carbon $rangeStart, Carbon $rangeEnd, string $rangeLabel): string
    {
        $esc = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $money = static fn ($value): string => 'PHP ' . number_format((float) $value, 2);

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
            .accepted { color:#047857; font-weight:bold; }
            .awaiting { color:#0e7490; font-weight:bold; }
            .pending { color:#b45309; font-weight:bold; }
            .rejected { color:#b91c1c; font-weight:bold; }
            .cancelled { color:#475569; font-weight:bold; }
            .kpi-main { background:#eff6ff; color:#1d4ed8; font-weight:bold; }
            .kpi-good { background:#ecfdf5; color:#047857; font-weight:bold; }
            .kpi-pend { background:#fffbeb; color:#b45309; font-weight:bold; }
            .footer { font-size:9pt; color:#64748b; font-style:italic; padding-top:8pt; }
        ';

        ob_start();
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Public Market Transactions Report</title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Market Report</x:Name>
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
    <tr><td colspan="12" class="title">Public Market Office</td></tr>
    <tr><td colspan="12" class="subtitle">Transactions &amp; Collection Report</td></tr>
    <tr><td colspan="12" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="12">&nbsp;</td></tr>
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
            <th style="width:25%;">Accepted</th>
            <th style="width:25%;">Pending / Awaiting</th>
            <th style="width:25%;">Total Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="kpi-main int center"><?= (int) $payload['totalTransactions'] ?></td>
            <td class="kpi-good int center">
                <?= (int) $payload['acceptedTransactions'] ?>
                &nbsp;(<?= $money($payload['acceptedAmount']) ?>)
            </td>
            <td class="kpi-pend int center">
                <?= (int) $payload['pendingTransactions'] ?> / <?= (int) $payload['awaitingTransactions'] ?>
                &nbsp;(<?= $money($payload['pendingAmount']) ?>)
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
    <tr><td colspan="7" class="section-title">WEEKLY SUMMARY</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th>Week</th>
            <th class="int">Transactions</th>
            <th class="int">Accepted</th>
            <th class="int">Pending</th>
            <th class="int">Awaiting</th>
            <th class="int">Cancelled</th>
            <th class="num">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($payload['weeklySummary'] as $row): $i++; ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($row['label']) ?></strong></td>
                <td class="int"><?= number_format((int) $row['transactions']) ?></td>
                <td class="int accepted"><?= number_format((int) $row['accepted']) ?></td>
                <td class="int pending"><?= number_format((int) $row['pending']) ?></td>
                <td class="int awaiting"><?= number_format((int) $row['awaiting']) ?></td>
                <td class="int cancelled"><?= number_format((int) $row['cancelled']) ?></td>
                <td class="num"><?= $money($row['total']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($payload['weeklySummary']->isEmpty()): ?>
            <tr><td colspan="7" class="center" style="color:#94a3b8;font-style:italic;">No weekly records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<br>
<?php endif; ?>

<?php if ($period === 'month'): ?>
<table>
    <tr><td colspan="7" class="section-title">MONTHLY SUMMARY</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th>Month</th>
            <th class="int">Transactions</th>
            <th class="int">Accepted</th>
            <th class="int">Pending</th>
            <th class="int">Awaiting</th>
            <th class="int">Cancelled</th>
            <th class="num">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($payload['monthlySummary'] as $row): $i++; ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($row['label']) ?></strong></td>
                <td class="int"><?= number_format((int) $row['transactions']) ?></td>
                <td class="int accepted"><?= number_format((int) $row['accepted']) ?></td>
                <td class="int pending"><?= number_format((int) $row['pending']) ?></td>
                <td class="int awaiting"><?= number_format((int) $row['awaiting']) ?></td>
                <td class="int cancelled"><?= number_format((int) $row['cancelled']) ?></td>
                <td class="num"><?= $money($row['total']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($payload['monthlySummary']->isEmpty()): ?>
            <tr><td colspan="7" class="center" style="color:#94a3b8;font-style:italic;">No monthly records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<br>
<?php endif; ?>

<table>
    <tr><td colspan="12" class="section-title">DETAILED TRANSACTIONS</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th>Record ID</th>
            <th>Stall</th>
            <th>Location</th>
            <th>Tenant</th>
            <th>Business</th>
            <th>Contract No.</th>
            <th>Payment No.</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th class="num">Amount</th>
            <th>Collector</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($payload['transactions'] as $row): $i++; ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($row['record_id']) ?></strong></td>
                <td><?= $esc($row['stall_no']) ?></td>
                <td><?= $esc($row['location']) ?></td>
                <td><?= $esc($row['tenant_name']) ?></td>
                <td><?= $esc($row['business_name']) ?></td>
                <td><?= $esc($row['contract_no']) ?></td>
                <td><?= $esc($row['payment_no']) ?></td>
                <td><?= $esc($row['date']) ?></td>
                <td><?= $esc($row['time']) ?></td>
                <td class="center <?= $esc(strtolower($row['status_key'])) ?>"><?= $esc($row['status']) ?></td>
                <td class="num"><?= $money($row['amount']) ?></td>
                <td><?= $esc($row['collector']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($payload['transactions']->isEmpty()): ?>
            <tr><td colspan="12" class="center" style="color:#94a3b8;font-style:italic;">No transactions found in the selected range.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<table>
    <tr><td colspan="12" class="footer">Public Market System &middot; Confidential Report &middot; Generated <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
</table>

</body>
</html>
        <?php

        return (string) ob_get_clean();
    }
}
