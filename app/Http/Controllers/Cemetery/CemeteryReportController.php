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
use Illuminate\View\View;

class CemeteryReportController extends Controller
{
    private const PAGE_MAX_ROWS = 150;
    private const PDF_MAX_ROWS = 100;

    public function index(Request $request): View
    {
        return view('cemetery.reports', $this->buildReportPayload($request, self::PAGE_MAX_ROWS));
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
        $from = trim((string) ($payload['dateFrom'] ?? ''));
        $to = trim((string) ($payload['dateTo'] ?? ''));
        $siteId = (int) ($payload['selectedSiteId'] ?? 0);
        $sitePart = $siteId > 0 ? 'site-' . $siteId : 'all-sites';
        $fromPart = $from !== '' ? str_replace('-', '', $from) : 'all';
        $toPart = $to !== '' ? str_replace('-', '', $to) : 'all';
        $filename = "cemetery-report-{$sitePart}-{$fromPart}-{$toPart}.pdf";

        return Pdf::loadView('cemetery.reports_pdf', [
            ...$payload,
            'generatedAt' => now(),
            'pdfMaxRows' => self::PDF_MAX_ROWS,
        ])->download($filename);
    }

    /**
     * @return array<string,mixed>
     */
    private function buildReportPayload(Request $request, int $rowLimit): array
    {
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        $siteId = (int) $request->query('cemetery_site_id', 0);

        $sites = CemeterySite::query()
            ->where('is_active', true)
            ->orderBy('site_name')
            ->get();

        $occupantQuery = CemeteryOccupantRecord::query()
            ->with(['site', 'plot', 'contact'])
            ->when($siteId > 0, fn ($query) => $query->where('cemetery_site_id', $siteId))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('date_of_interment', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('date_of_interment', '<=', $dateTo));

        $serviceQuery = CemeteryServiceLog::query()
            ->with(['site', 'serviceType'])
            ->when($siteId > 0, fn ($query) => $query->where('cemetery_site_id', $siteId))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('service_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('service_date', '<=', $dateTo));

        $transactionQuery = CemeteryTransaction::query()
            ->with(['site', 'category', 'transactionType'])
            ->when($siteId > 0, fn ($query) => $query->where('cemetery_site_id', $siteId))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('transaction_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('transaction_date', '<=', $dateTo));

        $paymentQuery = CemeteryPaymentCollection::query()
            ->with(['transaction.site', 'contact'])
            ->when($siteId > 0, function ($query) use ($siteId): void {
                $query->whereHas('transaction', fn ($tx) => $tx->where('cemetery_site_id', $siteId));
            })
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('payment_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('payment_date', '<=', $dateTo));

        $occupants = (clone $occupantQuery)->orderByDesc('date_of_interment')->limit($rowLimit)->get();
        $services = (clone $serviceQuery)->orderByDesc('service_date')->limit($rowLimit)->get();
        $transactions = (clone $transactionQuery)->orderByDesc('transaction_date')->limit($rowLimit)->get();
        $payments = (clone $paymentQuery)->orderByDesc('payment_date')->limit($rowLimit)->get();

        return [
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
}
