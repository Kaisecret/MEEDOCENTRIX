<?php

namespace App\Http\Controllers\Atrium;

use App\Http\Controllers\Controller;
use App\Models\AtriumEvent;
use App\Models\AtriumEventPayment;
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

    public function csv(Request $request)
    {
        [$report, $period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveFilters($request);
        $payload = $this->buildReportPayload($report, $rangeStart, $rangeEnd);
        $filename = 'atrium-' . $report . '-report-' . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->streamDownload(function () use ($payload, $report, $rangeStart, $rangeEnd, $rangeLabel): void {
            echo "\xEF\xBB\xBF";
            echo $this->renderExcelHtml($payload, $report, $rangeStart, $rangeEnd, $rangeLabel);
        }, $filename, $headers);
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function renderExcelHtml(array $payload, string $report, Carbon $rangeStart, Carbon $rangeEnd, string $rangeLabel): string
    {
        $esc = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $money = static fn ($value): string => 'PHP ' . number_format((float) $value, 2);
        $rows = $payload['rows'] ?? collect();
        if (! $rows instanceof Collection) {
            $rows = collect($rows);
        }

        $metricValue = (float) ($payload['metricValue'] ?? 0);
        $metricText = ($payload['metricIsCurrency'] ?? false)
            ? $money($metricValue)
            : number_format($metricValue);

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
            .good { color:#047857; font-weight:bold; }
            .warn { color:#b45309; font-weight:bold; }
            .bad { color:#b91c1c; font-weight:bold; }
            .footer { font-size:9pt; color:#64748b; font-style:italic; padding-top:8pt; }
        ';

        ob_start();
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Atrium Report</title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Atrium Report</x:Name>
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
    <tr><td colspan="10" class="title">Atrium Hall Management Office</td></tr>
    <tr><td colspan="10" class="subtitle"><?= $esc((string) ($payload['reportTitle'] ?? 'Atrium Report')) ?></td></tr>
    <tr><td colspan="10" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr><td colspan="10">&nbsp;</td></tr>
</table>

<table class="info">
    <tr>
        <th style="width:18%;">Period</th>
        <td style="width:32%;"><?= $esc($rangeLabel) ?></td>
        <th style="width:18%;">Total Records</th>
        <td style="width:32%;"><?= number_format((int) ($payload['totalRecords'] ?? 0)) ?> record(s)</td>
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
            <th>Total Records</th>
            <th><?= $esc((string) ($payload['primaryLabel'] ?? 'Primary')) ?></th>
            <th><?= $esc((string) ($payload['secondaryLabel'] ?? 'Secondary')) ?></th>
            <th><?= $esc((string) ($payload['metricLabel'] ?? 'Metric')) ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="int center"><?= number_format((int) ($payload['totalRecords'] ?? 0)) ?></td>
            <td class="int center good"><?= number_format((int) ($payload['primaryCount'] ?? 0)) ?></td>
            <td class="int center warn"><?= number_format((int) ($payload['secondaryCount'] ?? 0)) ?></td>
            <td class="center" style="font-weight:bold;color:#0c3a5b;"><?= $esc($metricText) ?></td>
        </tr>
    </tbody>
</table>

<br>
<table>
    <tr><td colspan="5" class="section-title">WEEKLY SUMMARY</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th>Week</th>
            <th class="int">Records</th>
            <th class="int"><?= $esc((string) ($payload['primaryLabel'] ?? 'Primary')) ?></th>
            <th class="int"><?= $esc((string) ($payload['secondaryLabel'] ?? 'Secondary')) ?></th>
            <th class="num"><?= $esc((string) ($payload['summaryTotalLabel'] ?? 'Total')) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach (($payload['weeklySummary'] ?? collect()) as $row): $i++; ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($row['label'] ?? '-') ?></strong></td>
                <td class="int"><?= number_format((int) ($row['records'] ?? 0)) ?></td>
                <td class="int"><?= number_format((int) ($row['primary'] ?? 0)) ?></td>
                <td class="int"><?= number_format((int) ($row['secondary'] ?? 0)) ?></td>
                <td class="num"><?= ($payload['metricIsCurrency'] ?? false) ? $money($row['total'] ?? 0) : number_format((float) ($row['total'] ?? 0)) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (($payload['weeklySummary'] ?? collect())->isEmpty()): ?>
            <tr><td colspan="5" class="center" style="color:#94a3b8;font-style:italic;">No weekly records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<br>
<table>
    <tr><td colspan="5" class="section-title">MONTHLY SUMMARY</td></tr>
</table>
<table class="data">
    <thead>
        <tr>
            <th>Month</th>
            <th class="int">Records</th>
            <th class="int"><?= $esc((string) ($payload['primaryLabel'] ?? 'Primary')) ?></th>
            <th class="int"><?= $esc((string) ($payload['secondaryLabel'] ?? 'Secondary')) ?></th>
            <th class="num"><?= $esc((string) ($payload['summaryTotalLabel'] ?? 'Total')) ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 0; foreach (($payload['monthlySummary'] ?? collect()) as $row): $i++; ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <td><strong><?= $esc($row['label'] ?? '-') ?></strong></td>
                <td class="int"><?= number_format((int) ($row['records'] ?? 0)) ?></td>
                <td class="int"><?= number_format((int) ($row['primary'] ?? 0)) ?></td>
                <td class="int"><?= number_format((int) ($row['secondary'] ?? 0)) ?></td>
                <td class="num"><?= ($payload['metricIsCurrency'] ?? false) ? $money($row['total'] ?? 0) : number_format((float) ($row['total'] ?? 0)) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (($payload['monthlySummary'] ?? collect())->isEmpty()): ?>
            <tr><td colspan="5" class="center" style="color:#94a3b8;font-style:italic;">No monthly records found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<br>
<table>
    <tr><td colspan="10" class="section-title">DETAILED REPORT</td></tr>
</table>
<table class="data">
    <thead>
        <?php if ($report === 'booking'): ?>
            <tr><th>Code</th><th>Date</th><th>Contact</th><th>Hall</th><th class="num">Hours</th><th class="num">Due</th><th>Status</th></tr>
        <?php else: ?>
            <tr><th>OR Number</th><th>Date</th><th>Event</th><th class="num">Amount</th><th>Status</th><th>Recorded By</th></tr>
        <?php endif; ?>
    </thead>
    <tbody>
        <?php $i = 0; foreach ($rows as $row): $i++; ?>
            <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
                <?php if ($report === 'booking'): ?>
                    <td><strong><?= $esc($row['code'] ?? '-') ?></strong></td>
                    <td><?= $esc($row['date'] ?? '-') ?></td>
                    <td><?= $esc($row['contact'] ?? '-') ?></td>
                    <td><?= $esc($row['hall'] ?? '-') ?></td>
                    <td class="num"><?= number_format((float) ($row['hours'] ?? 0), 2) ?></td>
                    <td class="num"><?= $money($row['amount'] ?? 0) ?></td>
                    <td class="<?= match ((string) ($row['status'] ?? '')) {
                        'confirmed', 'completed' => 'good',
                        'cancelled' => 'bad',
                        default => 'warn',
                    } ?>"><?= $esc($row['status_label'] ?? '-') ?></td>
                <?php else: ?>
                    <td><strong><?= $esc($row['or_number'] ?? '-') ?></strong></td>
                    <td><?= $esc($row['date'] ?? '-') ?></td>
                    <td><?= $esc($row['event'] ?? '-') ?></td>
                    <td class="num"><?= $money($row['amount'] ?? 0) ?></td>
                    <td class="<?= match ((string) ($row['status'] ?? '')) {
                        'paid' => 'good',
                        'unpaid' => 'bad',
                        default => 'warn',
                    } ?>"><?= $esc($row['status_label'] ?? '-') ?></td>
                    <td><?= $esc($row['recorded_by'] ?? '-') ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        <?php if ($rows->isEmpty()): ?>
            <tr><td colspan="10" class="center" style="color:#94a3b8;font-style:italic;">No records found in selected range.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<table>
    <tr><td colspan="10" class="footer">Atrium Hall Management System · Confidential Report · Generated <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
</table>
</body>
</html>
        <?php

        return (string) ob_get_clean();
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
        if (! in_array($value, ['booking', 'collection'], true)) {
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

}
