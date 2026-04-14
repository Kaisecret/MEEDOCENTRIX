<?php

namespace App\Http\Controllers\Cemetery;

use App\Http\Controllers\Controller;
use App\Models\CemeteryOccupantRecord;
use App\Models\CemeteryPaymentCollection;
use App\Models\CemeteryServiceLog;
use App\Models\CemeterySite;
use App\Models\CemeteryTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CemeteryReportController extends Controller
{
    public function index(Request $request): View
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

        $occupants = (clone $occupantQuery)->orderByDesc('date_of_interment')->limit(150)->get();
        $services = (clone $serviceQuery)->orderByDesc('service_date')->limit(150)->get();
        $transactions = (clone $transactionQuery)->orderByDesc('transaction_date')->limit(150)->get();
        $payments = (clone $paymentQuery)->orderByDesc('payment_date')->limit(150)->get();

        return view('cemetery.reports', [
            'sites' => $sites,
            'selectedSiteId' => $siteId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
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
        ]);
    }
}
