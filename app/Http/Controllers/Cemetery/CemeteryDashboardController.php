<?php

namespace App\Http\Controllers\Cemetery;

use App\Http\Controllers\Controller;
use App\Models\CemeteryOccupantRecord;
use App\Models\CemeteryPaymentCollection;
use App\Models\CemeteryPlot;
use App\Models\CemeteryServiceLog;
use App\Models\CemeteryTransaction;
use Illuminate\View\View;

class CemeteryDashboardController extends Controller
{
    public function index(): View
    {
        $recentTransactions = CemeteryTransaction::query()
            ->with(['site:id,site_name', 'transactionType:id,type_name'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('cemetery.dashboard', [
            'summary' => [
                'total_occupants' => CemeteryOccupantRecord::query()->count(),
                'occupied_plots' => CemeteryPlot::query()->where('is_occupied', true)->count(),
                'available_plots' => CemeteryPlot::query()->where('is_active', true)->where('is_occupied', false)->count(),
                'services_today' => CemeteryServiceLog::query()->whereDate('service_date', now()->toDateString())->count(),
                'transactions_today' => CemeteryTransaction::query()->whereDate('transaction_date', now()->toDateString())->count(),
                'payments_today' => (float) CemeteryPaymentCollection::query()
                    ->whereDate('payment_date', now()->toDateString())
                    ->sum('amount_paid'),
                'total_collected' => (float) CemeteryPaymentCollection::query()->sum('amount_paid'),
                'overdue_maintenance' => CemeteryOccupantRecord::query()->where('maintenance_fee_status', 'overdue')->count(),
            ],
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
