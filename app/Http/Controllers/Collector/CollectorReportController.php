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

    public function csv(Request $request)
    {
        $assignment = $this->collectorAssignment($request);
        $departmentCode = (string) ($assignment?->department?->code ?? '');
        [$period, $rangeStart, $rangeEnd, , , $rangeLabel] = $this->resolveRange($request);

        $payload = $departmentCode !== ''
            ? $this->buildReportPayload($request, $rangeStart, $rangeEnd, $departmentCode)
            : $this->emptyReportPayload('No Assignment');

        $prefix = $departmentCode === 'market' ? 'market-collector-report-' : 'collector-report-';
        $filename = $prefix . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->streamDownload(function () use ($payload, $period, $rangeStart, $rangeEnd, $rangeLabel, $departmentCode): void {
            echo "\xEF\xBB\xBF";
            echo $this->renderCollectorExcelHtml($payload, $period, $rangeStart, $rangeEnd, $rangeLabel, $departmentCode);
        }, $filename, $headers);
    }

    private function renderCollectorExcelHtml(
        array $payload,
        string $period,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        string $rangeLabel,
        string $departmentCode
    ): string {
        $esc = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $money = static fn ($v): string => 'PHP ' . number_format((float) $v, 2);

        $title = $departmentCode === 'market' ? 'Market Collector Report' : 'Collector Report';
        $subtitle = $departmentCode === 'market'
            ? 'Public Market Collections'
            : 'Fishport Collection Transactions';
        $sheetName = $departmentCode === 'market' ? 'Market Collector' : 'Collector Report';

        $css = '
            body { font-family: Calibri, "Segoe UI", Arial, sans-serif; color:#0f172a; }
            table { border-collapse: collapse; width: 100%; }
            .title { font-size:18pt; font-weight:bold; color:#0c3a5b; }
            .subtitle { font-size:11pt; color:#475569; }
            .meta { font-size:10pt; color:#475569; }
            .section-title { background:#0c3a5b; color:#fff; font-weight:bold; padding:6pt 10pt; font-size:11pt; letter-spacing:1pt; }
            .info th { background:#eaf2f9; color:#0c3a5b; text-align:left; font-weight:bold; padding:6pt 10pt; border:1px solid #cbd5e1; }
            .info td { background:#f8fafc; padding:6pt 10pt; border:1px solid #cbd5e1; font-weight:bold; }
            .data th { background:#155f8f; color:#fff; font-weight:bold; padding:6pt 8pt; border:1px solid #0c3a5b; text-align:left; font-size:10pt; }
            .data td { padding:5pt 8pt; border:1px solid #cbd5e1; font-size:10pt; vertical-align:top; }
            .data tr.alt td { background:#f8fafc; }
            .num { mso-number-format:"#,##0.00"; text-align:right; }
            .int { mso-number-format:"#,##0"; text-align:right; }
            .center { text-align:center; }
            .accepted { color:#047857; font-weight:bold; }
            .awaiting { color:#0e7490; font-weight:bold; }
            .pending { color:#b45309; font-weight:bold; }
            .rejected { color:#b91c1c; font-weight:bold; }
            .kpi-ok { background:#ecfdf5; color:#047857; font-weight:bold; }
            .kpi-pending { background:#fffbeb; color:#b45309; font-weight:bold; }
            .kpi-total { background:#eff6ff; color:#0c3a5b; font-weight:bold; }
            .footer { font-size:9pt; color:#64748b; font-style:italic; padding-top:8pt; }
        ';

        $statusClass = static function (string $statusKey): string {
            return match ($statusKey) {
                'accepted' => 'accepted',
                'collected_pending_confirmation' => 'awaiting',
                'rejected' => 'rejected',
                default => 'pending',
            };
        };

        ob_start();
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title><?= $esc($title) ?></title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name><?= $esc($sheetName) ?></x:Name>
                    <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style><?= $css ?></style>
</head>
<body>
<table>
    <tr><td colspan="11" class="title"><?= $esc($title) ?></td></tr>
    <tr><td colspan="11" class="subtitle"><?= $esc($subtitle) ?></td></tr>
    <tr><td colspan="11" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="11">&nbsp;</td></tr>
</table>

<table class="info">
    <tr>
        <th style="width:18%;">Period</th>
        <td style="width:32%;"><?= $esc($rangeLabel) ?></td>
        <th style="width:18%;">Total Records</th>
        <td style="width:32%;"><?= number_format((int) ($payload['totalTransactions'] ?? 0)) ?> transaction(s)</td>
    </tr>
    <tr>
        <th>Date From</th>
        <td><?= $esc($rangeStart->format('F d, Y')) ?></td>
        <th>Date To</th>
        <td><?= $esc($rangeEnd->format('F d, Y')) ?></td>
    </tr>
</table>

<br>

<table><tr><td colspan="4" class="section-title">SUMMARY</td></tr></table>
<table class="data">
    <thead>
        <tr>
            <th style="width:25%;">Total Transactions</th>
            <th style="width:25%;">Accepted Transactions</th>
            <th style="width:25%;">Pending Transactions</th>
            <th style="width:25%;">Total Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="kpi-total int center"><?= (int) ($payload['totalTransactions'] ?? 0) ?></td>
            <td class="kpi-ok int center">
                <?= (int) ($payload['acceptedTransactions'] ?? 0) ?>
                &nbsp;(<?= $money($payload['acceptedAmount'] ?? 0) ?>)
            </td>
            <td class="kpi-pending int center">
                <?= (int) ($payload['pendingTransactions'] ?? 0) ?>
                &nbsp;(<?= $money($payload['pendingAmount'] ?? 0) ?>)
            </td>
            <td class="num center" style="font-weight:bold;color:#0c3a5b;"><?= $money($payload['totalAmount'] ?? 0) ?></td>
        </tr>
    </tbody>
</table>

<br>

<?php if ($period === 'week'): ?>
<table><tr><td colspan="5" class="section-title">WEEKLY SUMMARY</td></tr></table>
<table class="data">
    <thead><tr><th>Week</th><th class="int">Transactions</th><th class="int">Accepted</th><th class="int">Pending</th><th class="num">Total</th></tr></thead>
    <tbody>
    <?php $i = 0; foreach (($payload['weeklySummary'] ?? collect()) as $row): $i++; ?>
        <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
            <td><strong><?= $esc($row['label']) ?></strong></td>
            <td class="int"><?= number_format((int) ($row['transactions'] ?? 0)) ?></td>
            <td class="int accepted"><?= number_format((int) ($row['accepted'] ?? 0)) ?></td>
            <td class="int pending"><?= number_format((int) ($row['pending'] ?? 0)) ?></td>
            <td class="num"><?= $money($row['total'] ?? 0) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (($payload['weeklySummary'] ?? collect())->isEmpty()): ?>
        <tr><td colspan="5" class="center" style="color:#94a3b8;font-style:italic;">No weekly records found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<br>
<?php endif; ?>

<?php if ($period === 'month'): ?>
<table><tr><td colspan="5" class="section-title">MONTHLY SUMMARY</td></tr></table>
<table class="data">
    <thead><tr><th>Month</th><th class="int">Transactions</th><th class="int">Accepted</th><th class="int">Pending</th><th class="num">Total</th></tr></thead>
    <tbody>
    <?php $i = 0; foreach (($payload['monthlySummary'] ?? collect()) as $row): $i++; ?>
        <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
            <td><strong><?= $esc($row['label']) ?></strong></td>
            <td class="int"><?= number_format((int) ($row['transactions'] ?? 0)) ?></td>
            <td class="int accepted"><?= number_format((int) ($row['accepted'] ?? 0)) ?></td>
            <td class="int pending"><?= number_format((int) ($row['pending'] ?? 0)) ?></td>
            <td class="num"><?= $money($row['total'] ?? 0) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (($payload['monthlySummary'] ?? collect())->isEmpty()): ?>
        <tr><td colspan="5" class="center" style="color:#94a3b8;font-style:italic;">No monthly records found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
<br>
<?php endif; ?>

<table><tr><td colspan="11" class="section-title">DETAILED TRANSACTIONS</td></tr></table>
<table class="data">
    <thead>
    <?php if ($departmentCode === 'market'): ?>
        <tr>
            <th>Stall</th><th>Tenant</th><th>Business</th><th>Payment No.</th><th>Date</th><th>Time</th><th>Status</th><th class="num">Total</th><th>Payer</th><th>Collector</th>
        </tr>
    <?php else: ?>
        <tr>
            <th>Log ID</th><th>Payment No.</th><th>Date</th><th>Time</th><th>Vessel</th><th class="center">ARR/DEP</th><th>Origin</th><th class="center">Status</th><th class="num">Total</th><th>Payer</th><th>Collector</th>
        </tr>
    <?php endif; ?>
    </thead>
    <tbody>
    <?php $i = 0; foreach (($payload['transactions'] ?? collect()) as $row): $i++; ?>
        <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
            <?php if ($departmentCode === 'market'): ?>
                <td><strong><?= $esc($row['stall_no'] ?? '-') ?></strong></td>
                <td><?= $esc($row['tenant_name'] ?? '-') ?></td>
                <td><?= $esc($row['business_name'] ?? '-') ?></td>
                <td><?= $esc($row['payment_no'] ?? '-') ?></td>
                <td><?= $esc($row['date'] ?? '-') ?></td>
                <td><?= $esc($row['time'] ?? '-') ?></td>
                <td class="center <?= $statusClass((string) ($row['status_key'] ?? '')) ?>"><?= $esc($row['status'] ?? 'Pending') ?></td>
                <td class="num"><?= $money($row['amount'] ?? 0) ?></td>
                <td><?= $esc($row['payer_name'] ?? '-') ?></td>
                <td><?= $esc($row['collector'] ?? '-') ?></td>
            <?php else: ?>
                <td><strong><?= $esc($row['log_id'] ?? '-') ?></strong></td>
                <td><?= $esc($row['payment_no'] ?? '-') ?></td>
                <td><?= $esc($row['date'] ?? '-') ?></td>
                <td><?= $esc($row['time'] ?? '-') ?></td>
                <td><?= $esc($row['vessel'] ?? '-') ?></td>
                <td class="center"><?= $esc($row['arr_dep'] ?? '-') ?></td>
                <td><?= $esc($row['origin'] ?? '-') ?></td>
                <td class="center <?= $statusClass((string) ($row['status_key'] ?? '')) ?>"><?= $esc($row['status'] ?? 'Pending') ?></td>
                <td class="num"><?= $money($row['amount'] ?? 0) ?></td>
                <td><?= $esc($row['payer_name'] ?? '-') ?></td>
                <td><?= $esc($row['collector'] ?? '-') ?></td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    <?php if (($payload['transactions'] ?? collect())->isEmpty()): ?>
        <tr><td colspan="11" class="center" style="color:#94a3b8;font-style:italic;">No transactions found in the selected range.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<table>
    <tr><td colspan="11" class="footer">Collector Management System &middot; Confidential Report &middot; Generated <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
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
