<?php

namespace App\Http\Controllers\Cemetery;

use App\Http\Controllers\Controller;
use App\Models\CemeteryOccupantRecord;
use App\Models\CemeteryPaymentCollection;
use App\Models\CemeteryServiceLog;
use App\Models\CemeterySite;
use App\Models\CemeteryTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CemeteryReportController extends Controller
{
    private const PAGE_MAX_ROWS = 150;
    private const PDF_MAX_ROWS = 100;
    private const CSV_MAX_ROWS = 2000;

    public function index(Request $request): View
    {
        $payload = $this->buildReportPayload($request, self::PAGE_MAX_ROWS);

        return view('cemetery.reports', $payload);
    }

    public function preview(Request $request): View
    {
        $payload = $this->buildReportPayload($request, self::PDF_MAX_ROWS);

        return view('cemetery.reports_pdf', [
            ...$payload,
            'generatedAt' => now(),
            'pdfMaxRows' => self::PDF_MAX_ROWS,
        ]);
    }

    public function pdf(Request $request)
    {
        $payload = $this->buildReportPayload($request, self::PDF_MAX_ROWS);
        $siteId = (int) ($payload['selectedSiteId'] ?? 0);
        /** @var Carbon $rangeStart */
        $rangeStart = $payload['rangeStart'];
        /** @var Carbon $rangeEnd */
        $rangeEnd = $payload['rangeEnd'];

        $sitePart = $siteId > 0 ? 'site-' . $siteId : 'all-sites';
        $filename = 'cemetery-report-' . $sitePart . '-' . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.pdf';

        return Pdf::loadView('cemetery.reports_pdf', [
            ...$payload,
            'generatedAt' => now(),
            'pdfMaxRows' => self::PDF_MAX_ROWS,
        ])->download($filename);
    }

    public function csv(Request $request): StreamedResponse
    {
        $payload = $this->buildReportPayload($request, self::CSV_MAX_ROWS);
        /** @var Carbon $rangeStart */
        $rangeStart = $payload['rangeStart'];
        /** @var Carbon $rangeEnd */
        $rangeEnd = $payload['rangeEnd'];

        $filename = 'cemetery-report-' . $rangeStart->format('Ymd') . '-' . $rangeEnd->format('Ymd') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];

        return response()->streamDownload(function () use ($payload): void {
            echo "\xEF\xBB\xBF";
            echo $this->renderExcelHtml($payload);
        }, $filename, $headers);
    }

    /**
     * @return array{0:string,1:Carbon,2:Carbon,3:string,4:string,5:string}
     */
    private function resolveRange(Request $request): array
    {
        $period = strtolower((string) $request->query('period', 'all'));
        if (! in_array($period, ['all', 'week', 'month', 'range'], true)) {
            $period = 'all';
        }

        $today = Carbon::today();
        $fromParsed = $this->parseDate((string) $request->query('date_from', ''));
        $toParsed = $this->parseDate((string) $request->query('date_to', ''));

        if ($period === 'all') {
            $start = Carbon::create(2000, 1, 1)->startOfDay();
            $end = $today->copy()->endOfDay();
            $label = 'All Time';
            $dateFromValue = '';
            $dateToValue = '';
        } elseif ($period === 'week') {
            $start = $today->copy()->startOfWeek();
            $end = $today->copy()->endOfWeek();
            $label = 'This Week';
            $dateFromValue = $start->toDateString();
            $dateToValue = $end->toDateString();
        } elseif ($period === 'range' && $fromParsed && $toParsed) {
            $start = $fromParsed->copy()->startOfDay();
            $end = $toParsed->copy()->endOfDay();
            if ($end->lt($start)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }
            $label = 'Custom Range';
            $dateFromValue = $start->toDateString();
            $dateToValue = $end->toDateString();
        } else {
            $period = 'month';
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfMonth();
            $label = 'This Month';
            $dateFromValue = $start->toDateString();
            $dateToValue = $end->toDateString();
        }

        return [
            $period,
            $start,
            $end,
            $dateFromValue,
            $dateToValue,
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
    private function buildReportPayload(Request $request, int $rowLimit): array
    {
        [$period, $rangeStart, $rangeEnd, $dateFrom, $dateTo, $rangeLabel] = $this->resolveRange($request);
        $siteId = (int) $request->query('cemetery_site_id', 0);

        $sites = CemeterySite::query()
            ->orderBy('site_name')
            ->get();

        $occupantQuery = CemeteryOccupantRecord::query()
            ->with(['site', 'plot', 'contact'])
            ->when($siteId > 0, fn ($query) => $query->where('cemetery_site_id', $siteId))
            ->whereDate('date_of_interment', '>=', $rangeStart->toDateString())
            ->whereDate('date_of_interment', '<=', $rangeEnd->toDateString());

        $serviceQuery = CemeteryServiceLog::query()
            ->with(['site', 'serviceType'])
            ->when($siteId > 0, fn ($query) => $query->where('cemetery_site_id', $siteId))
            ->whereDate('service_date', '>=', $rangeStart->toDateString())
            ->whereDate('service_date', '<=', $rangeEnd->toDateString());

        $transactionQuery = CemeteryTransaction::query()
            ->with(['site', 'category', 'transactionType'])
            ->when($siteId > 0, fn ($query) => $query->where('cemetery_site_id', $siteId))
            ->whereDate('transaction_date', '>=', $rangeStart->toDateString())
            ->whereDate('transaction_date', '<=', $rangeEnd->toDateString());

        $paymentQuery = CemeteryPaymentCollection::query()
            ->with(['transaction.site', 'contact'])
            ->when($siteId > 0, function ($query) use ($siteId): void {
                $query->whereHas('transaction', fn ($tx) => $tx->where('cemetery_site_id', $siteId));
            })
            ->whereDate('payment_date', '>=', $rangeStart->toDateString())
            ->whereDate('payment_date', '<=', $rangeEnd->toDateString());

        $occupants = (clone $occupantQuery)->orderByDesc('date_of_interment')->limit($rowLimit)->get();
        $services = (clone $serviceQuery)->orderByDesc('service_date')->limit($rowLimit)->get();
        $transactions = (clone $transactionQuery)->orderByDesc('transaction_date')->limit($rowLimit)->get();
        $payments = (clone $paymentQuery)->orderByDesc('payment_date')->limit($rowLimit)->get();

        return [
            'period' => $period,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'rangeLabel' => $rangeLabel,
            'sites' => $sites,
            'selectedSiteId' => $siteId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rowLimit' => $rowLimit,
            'summary' => [
                'occupant_total' => (clone $occupantQuery)->count(),
                'service_total' => (clone $serviceQuery)->count(),
                'transaction_total' => (clone $transactionQuery)->count(),
                'payment_total' => (clone $paymentQuery)->count(),
                'amount_due_total' => (float) (clone $transactionQuery)->sum('amount_due'),
                'amount_collected_total' => (float) (clone $paymentQuery)->sum('amount_paid'),
                'overdue_maintenance_total' => (clone $occupantQuery)->where('maintenance_fee_status', 'overdue')->count(),
                'overdue_payment_total' => (clone $paymentQuery)->where('payment_status', 'overdue')->count(),
            ],
            'occupants' => $occupants,
            'services' => $services,
            'transactions' => $transactions,
            'payments' => $payments,
        ];
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function renderExcelHtml(array $payload): string
    {
        $esc = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $money = static fn ($v): string => 'PHP ' . number_format((float) $v, 2);

        $summary = $payload['summary'];
        $occupants = $payload['occupants'];
        $transactions = $payload['transactions'];
        $payments = $payload['payments'];

        $css = '
            body { font-family: Calibri, "Segoe UI", Arial, sans-serif; color:#0f172a; }
            table { border-collapse: collapse; width: 100%; }
            .title { font-size:18pt; font-weight:bold; color:#0c3a5b; }
            .subtitle { font-size:11pt; color:#475569; }
            .meta { font-size:10pt; color:#475569; }
            .section-title { background:#0c3a5b; color:#ffffff; font-weight:bold; padding:6pt 10pt; font-size:11pt; letter-spacing:1pt; }
            .data th { background:#155f8f; color:#ffffff; font-weight:bold; padding:6pt 8pt; border:1px solid #0c3a5b; text-align:left; font-size:10pt; }
            .data td { padding:5pt 8pt; border:1px solid #cbd5e1; font-size:10pt; vertical-align:top; }
            .data tr.alt td { background:#f8fafc; }
            .num { text-align:right; mso-number-format:"#,##0.00"; }
            .center { text-align:center; }
        ';

        ob_start();
        ?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <title>Cemetery Reports</title>
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Cemetery Reports</x:Name>
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
    <tr><td colspan="8" class="title">Cemetery Reports</td></tr>
    <tr><td colspan="8" class="subtitle">Summary and detailed records</td></tr>
    <tr><td colspan="8" class="meta">Generated: <?= $esc(now()->format('F d, Y h:i A')) ?></td></tr>
    <tr>
        <td colspan="8" class="meta">
            Period: <?= $esc($payload['rangeLabel']) ?>
            <?php if ((string) ($payload['period'] ?? '') !== 'all'): ?>
                (<?= $esc($payload['dateFrom']) ?> to <?= $esc($payload['dateTo']) ?>)
            <?php endif; ?>
        </td>
    </tr>
    <tr><td colspan="8">&nbsp;</td></tr>
</table>

<table class="data">
    <thead>
        <tr>
            <th>Occupant Records</th>
            <th>Service Logs</th>
            <th>Transactions</th>
            <th>Payments</th>
            <th>Total Amount Due</th>
            <th>Total Collected</th>
            <th>Overdue Maintenance</th>
            <th>Overdue Payments</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="center"><?= number_format((int) $summary['occupant_total']) ?></td>
            <td class="center"><?= number_format((int) $summary['service_total']) ?></td>
            <td class="center"><?= number_format((int) $summary['transaction_total']) ?></td>
            <td class="center"><?= number_format((int) $summary['payment_total']) ?></td>
            <td class="num"><?= $money($summary['amount_due_total']) ?></td>
            <td class="num"><?= $money($summary['amount_collected_total']) ?></td>
            <td class="center"><?= number_format((int) $summary['overdue_maintenance_total']) ?></td>
            <td class="center"><?= number_format((int) $summary['overdue_payment_total']) ?></td>
        </tr>
    </tbody>
</table>

<br>
<table><tr><td class="section-title">OCCUPANT MAINTENANCE REPORT</td></tr></table>
<table class="data">
    <thead><tr><th>Record No.</th><th>Cemetery</th><th>Deceased</th><th>Niche/Lot</th><th>Contact</th><th>Maintenance</th><th>Coverage End</th></tr></thead>
    <tbody>
    <?php $i = 0; foreach ($occupants as $record): $i++; ?>
        <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
            <td><?= $esc($record->record_no) ?></td>
            <td><?= $esc($record->site?->site_name ?: '-') ?></td>
            <td><?= $esc($record->deceased_name ?: '-') ?></td>
            <td><?= $esc($record->plot?->plot_reference ?: '-') ?></td>
            <td><?= $esc($record->contact?->contact_person ?: '-') ?></td>
            <td><?= $esc(strtoupper((string) $record->maintenance_fee_status)) ?></td>
            <td><?= $esc(optional($record->coverage_end_date)->format('Y-m-d') ?: '-') ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($occupants->isEmpty()): ?>
        <tr><td colspan="7">No occupant data for selected filter.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<br>
<table><tr><td class="section-title">CEMETERY TRANSACTIONS REPORT</td></tr></table>
<table class="data">
    <thead><tr><th>Transaction No.</th><th>Date</th><th>Cemetery</th><th>Type</th><th>Deceased</th><th>Amount Due</th><th>Status</th></tr></thead>
    <tbody>
    <?php $i = 0; foreach ($transactions as $transaction): $i++; ?>
        <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
            <td><?= $esc($transaction->transaction_no) ?></td>
            <td><?= $esc(optional($transaction->transaction_date)->format('Y-m-d') ?: '-') ?></td>
            <td><?= $esc($transaction->site?->site_name ?: '-') ?></td>
            <td><?= $esc($transaction->transactionType?->type_name ?: '-') ?></td>
            <td><?= $esc($transaction->deceased_name ?: '-') ?></td>
            <td class="num"><?= $money($transaction->amount_due) ?></td>
            <td><?= $esc(strtoupper((string) $transaction->status)) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($transactions->isEmpty()): ?>
        <tr><td colspan="7">No transaction data for selected filter.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<br>
<table><tr><td class="section-title">PAYMENT COLLECTION REPORT</td></tr></table>
<table class="data">
    <thead><tr><th>Payment Ref.</th><th>Transaction Ref.</th><th>Cemetery</th><th>OR No.</th><th>Payment Date</th><th>Amount Paid</th><th>Status</th></tr></thead>
    <tbody>
    <?php $i = 0; foreach ($payments as $payment): $i++; ?>
        <tr<?= $i % 2 === 0 ? ' class="alt"' : '' ?>>
            <td><?= $esc($payment->payment_no) ?></td>
            <td><?= $esc($payment->transaction?->transaction_no ?: '-') ?></td>
            <td><?= $esc($payment->transaction?->site?->site_name ?: '-') ?></td>
            <td><?= $esc($payment->official_receipt_no ?: '-') ?></td>
            <td><?= $esc(optional($payment->payment_date)->format('Y-m-d') ?: '-') ?></td>
            <td class="num"><?= $money($payment->amount_paid) ?></td>
            <td><?= $esc(strtoupper((string) $payment->payment_status)) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if ($payments->isEmpty()): ?>
        <tr><td colspan="7">No payment data for selected filter.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</body>
</html>
        <?php

        return (string) ob_get_clean();
    }
}
