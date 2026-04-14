<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\FishportRateController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Collector\CollectorCollectionController;
use App\Http\Controllers\Collector\CollectorReportController;
use App\Http\Controllers\Collector\CollectorProfileController;
use App\Http\Controllers\Cemetery\CemeteryOccupantRecordController;
use App\Http\Controllers\Cemetery\CemeteryPaymentCollectionController;
use App\Http\Controllers\Cemetery\CemeteryDashboardController;
use App\Http\Controllers\Cemetery\CemeteryReportController;
use App\Http\Controllers\Cemetery\CemeteryServiceLogController;
use App\Http\Controllers\Cemetery\CemeteryTransactionController;
use App\Http\Controllers\Fishport\FishportDashboardController;
use App\Http\Controllers\Fishport\FishportLogController;
use App\Http\Controllers\Fishport\FishportProfileController;
use App\Http\Controllers\Fishport\FishportReportController;
use App\Http\Controllers\Fishport\FishportSendPaymentController;
use App\Http\Controllers\Fishport\FishportVesselRegistryController;
use App\Http\Controllers\Market\MarketProfileController;
use App\Http\Controllers\Market\MarketStallController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.store');
Route::get('/admin-login', [LoginController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin-login', [LoginController::class, 'adminLogin'])->name('admin.login.store');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    // Admin
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
        Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users');
        Route::post('/users', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::post('/users/collector-assignments', [UserManagementController::class, 'assignCollector'])->name('admin.users.collector_assignments.store');
        Route::get('/rates', [FishportRateController::class, 'index'])->name('admin.rates');
        Route::put('/rates', [FishportRateController::class, 'update'])->name('admin.rates.update');
        Route::view('/roles', 'admin.roles')->name('admin.roles');
        Route::view('/reports', 'admin.reports')->name('admin.reports');
        Route::view('/transactions', 'admin.transactions')->name('admin.transactions');
    });

    // Fishport
    Route::prefix('fishport')->middleware('area:fishport')->group(function () {
        Route::get('/dashboard', [FishportDashboardController::class, 'index'])->name('fishport.dashboard');
        Route::get('/vessel-logs', [FishportLogController::class, 'vesselLogs'])->name('fishport.vessel_logs');
        Route::post('/vessel-logs', [FishportLogController::class, 'storeVesselLog'])->name('fishport.vessel_logs.store');
        Route::patch('/vessel-logs/{fishportLog}', [FishportLogController::class, 'updateVesselLog'])->name('fishport.vessel_logs.update');
        Route::get('/vessel-registry', [FishportVesselRegistryController::class, 'index'])->name('fishport.vessel_registry');
        Route::post('/vessel-registry', [FishportVesselRegistryController::class, 'store'])->name('fishport.vessel_registry.store');
        Route::put('/vessel-registry/{fishportVessel}', [FishportVesselRegistryController::class, 'update'])->name('fishport.vessel_registry.update');
        Route::patch('/vessel-registry/{fishportVessel}/toggle-active', [FishportVesselRegistryController::class, 'toggleActive'])->name('fishport.vessel_registry.toggle_active');
        Route::delete('/vessel-registry/{fishportVessel}', [FishportVesselRegistryController::class, 'destroy'])->name('fishport.vessel_registry.destroy');
        Route::get('/send-payment', [FishportSendPaymentController::class, 'index'])->name('fishport.send_payment');
        Route::post('/send-payment', [FishportSendPaymentController::class, 'store'])->name('fishport.send_payment.store');
        Route::patch('/send-payment/items/{dispatchItem}/cancel', [FishportSendPaymentController::class, 'cancel'])->name('fishport.send_payment.items.cancel');
        Route::patch('/send-payment/items/{dispatchItem}/approve', [FishportSendPaymentController::class, 'approve'])->name('fishport.send_payment.items.approve');
        Route::patch('/send-payment/items/{dispatchItem}/reject', [FishportSendPaymentController::class, 'reject'])->name('fishport.send_payment.items.reject');
        Route::get('/profile', [FishportProfileController::class, 'show'])->name('fishport.profile');
        Route::put('/profile', [FishportProfileController::class, 'update'])->name('fishport.profile.update');
        Route::get('/reports', [FishportReportController::class, 'index'])->name('fishport.reports');
        Route::get('/reports/preview', [FishportReportController::class, 'preview'])->name('fishport.reports.preview');
        Route::get('/reports/pdf', [FishportReportController::class, 'pdf'])->name('fishport.reports.pdf');
        Route::get('/records', [FishportLogController::class, 'index'])->name('fishport.records');
        Route::post('/records', [FishportLogController::class, 'store'])->name('fishport.records.store');
        Route::get('/records/{fishportLog}/receipt', [FishportLogController::class, 'downloadReceipt'])->name('fishport.records.receipt');
        Route::get('/records/{fishportLog}/receipt/pdf', [FishportLogController::class, 'downloadReceiptPdf'])->name('fishport.records.receipt.pdf');
        Route::get('/records/{fishportLog}/edit', [FishportLogController::class, 'edit'])->name('fishport.records.edit');
        Route::put('/records/{fishportLog}', [FishportLogController::class, 'update'])->name('fishport.records.update');
        Route::patch('/records/{fishportLog}/mark-paid', [FishportLogController::class, 'markPaid'])->name('fishport.records.mark_paid');
        Route::patch('/records/{fishportLog}/cancel-payment', [FishportLogController::class, 'cancelPayment'])->name('fishport.records.cancel_payment');
        Route::delete('/records/{fishportLog}', [FishportLogController::class, 'destroy'])->name('fishport.records.destroy');
    });

    // Market
    Route::prefix('market')->middleware('area:market')->group(function () {
        Route::view('/dashboard', 'market.dashboard')->name('market.dashboard');
        Route::view('/vendors', 'market.vendors')->name('market.vendors');
        Route::get('/stalls', [MarketStallController::class, 'index'])->name('market.stalls');
        Route::post('/stalls', [MarketStallController::class, 'store'])->name('market.stalls.store');
        Route::put('/stalls/{marketStall}', [MarketStallController::class, 'update'])->name('market.stalls.update');
        Route::delete('/stalls/{marketStall}', [MarketStallController::class, 'destroy'])->name('market.stalls.destroy');
        Route::post('/stalls/locations', [MarketStallController::class, 'storeLocation'])->name('market.stalls.locations.store');
        Route::post('/stalls/rates', [MarketStallController::class, 'storeLocationRate'])->name('market.stalls.rates.store');
        Route::get('/profile', [MarketProfileController::class, 'show'])->name('market.profile');
        Route::put('/profile', [MarketProfileController::class, 'update'])->name('market.profile.update');
        Route::view('/send-payment', 'shared.send_payment')->name('market.send_payment');
        Route::view('/records', 'market.transactions')->name('market.records');
    });

    // Cemetery
    Route::prefix('cemetery')->middleware('area:cemetery')->group(function () {
        Route::get('/dashboard', [CemeteryDashboardController::class, 'index'])->name('cemetery.dashboard');
        Route::get('/records', [CemeteryOccupantRecordController::class, 'index'])->name('cemetery.records');
        Route::post('/records', [CemeteryOccupantRecordController::class, 'store'])->name('cemetery.records.store');
        Route::put('/records/{occupantRecord}', [CemeteryOccupantRecordController::class, 'update'])->name('cemetery.records.update');
        Route::delete('/records/{occupantRecord}', [CemeteryOccupantRecordController::class, 'destroy'])->name('cemetery.records.destroy');
        Route::get('/services', [CemeteryServiceLogController::class, 'index'])->name('cemetery.services');
        Route::post('/services', [CemeteryServiceLogController::class, 'store'])->name('cemetery.services.store');
        Route::put('/services/{serviceLog}', [CemeteryServiceLogController::class, 'update'])->name('cemetery.services.update');
        Route::delete('/services/{serviceLog}', [CemeteryServiceLogController::class, 'destroy'])->name('cemetery.services.destroy');
        Route::get('/transactions', [CemeteryTransactionController::class, 'index'])->name('cemetery.transactions');
        Route::post('/transactions', [CemeteryTransactionController::class, 'store'])->name('cemetery.transactions.store');
        Route::put('/transactions/{transaction}', [CemeteryTransactionController::class, 'update'])->name('cemetery.transactions.update');
        Route::delete('/transactions/{transaction}', [CemeteryTransactionController::class, 'destroy'])->name('cemetery.transactions.destroy');
        Route::get('/payments', [CemeteryPaymentCollectionController::class, 'index'])->name('cemetery.payments');
        Route::post('/payments', [CemeteryPaymentCollectionController::class, 'store'])->name('cemetery.payments.store');
        Route::put('/payments/{paymentCollection}', [CemeteryPaymentCollectionController::class, 'update'])->name('cemetery.payments.update');
        Route::delete('/payments/{paymentCollection}', [CemeteryPaymentCollectionController::class, 'destroy'])->name('cemetery.payments.destroy');
        Route::get('/reports', [CemeteryReportController::class, 'index'])->name('cemetery.reports');
    });

    // Terminal
    Route::prefix('terminal')->middleware('area:terminal')->group(function () {
        Route::view('/dashboard', 'terminal.dashboard')->name('terminal.dashboard');
        Route::view('/vehicles', 'terminal.vehicles')->name('terminal.vehicles');
        Route::view('/send-payment', 'shared.send_payment')->name('terminal.send_payment');
        Route::view('/records', 'terminal.transactions')->name('terminal.records');
    });

    // Atrium
    Route::prefix('atrium')->middleware('area:atrium')->group(function () {
        Route::view('/dashboard', 'atrium.dashboard')->name('atrium.dashboard');
        Route::view('/booking', 'atrium.booking')->name('atrium.booking');
        Route::view('/records', 'atrium.records')->name('atrium.records');
        Route::view('/calendar', 'atrium.calendar')->name('atrium.calendar');
    });

    // Collector
    Route::prefix('collector')->middleware('area:collector')->group(function () {
        Route::get('/dashboard', [CollectorCollectionController::class, 'dashboard'])->name('collector.dashboard');
        Route::get('/pending-collections', [CollectorCollectionController::class, 'pendingCollections'])->name('collector.pending_collections');
        Route::post('/pending-collections/{dispatchItem}/collect', [CollectorCollectionController::class, 'collect'])->name('collector.pending_collections.collect');
        Route::get('/payments', [CollectorCollectionController::class, 'payments'])->name('collector.payments');
        Route::get('/reports', [CollectorReportController::class, 'index'])->name('collector.reports');
        Route::get('/reports/preview', [CollectorReportController::class, 'preview'])->name('collector.reports.preview');
        Route::get('/reports/pdf', [CollectorReportController::class, 'pdf'])->name('collector.reports.pdf');
        Route::get('/profile', [CollectorProfileController::class, 'show'])->name('collector.profile');
        Route::put('/profile', [CollectorProfileController::class, 'update'])->name('collector.profile.update');
        Route::view('/remit', 'collector.remit')->name('collector.remit');
    });

    // Shared secure image proof viewer for collector + fishport personnel
    Route::get('/collection-proofs/{dispatchItem}', [CollectorCollectionController::class, 'proofImage'])
        ->name('collection.proof');

    // Cashier
    Route::prefix('cashier')->middleware('area:cashier')->group(function () {
        Route::view('/dashboard', 'cashier.dashboard')->name('cashier.dashboard');
        Route::view('/remittance', 'cashier.remittance')->name('cashier.remittance');
        Route::view('/collections', 'cashier.collections')->name('cashier.collections');
        Route::view('/summary', 'cashier.summary')->name('cashier.summary');
    });

    // Shared
    Route::view('/profile', 'shared.profile')->name('profile');
    Route::view('/settings', 'shared.settings')->name('settings');
    Route::view('/notifications', 'shared.notifications')->name('notifications');
    Route::get('/send-payment', static function (Request $request) {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        return redirect()->route(match ($user->uiRoleKey()) {
            'fishport' => 'fishport.send_payment',
            'market' => 'market.send_payment',
            'terminal' => 'terminal.send_payment',
            default => $user->dashboardRouteName(),
        });
    })->name('send_payment');
    Route::view('/direct-payment', 'shared.direct_payment')->name('direct_payment');
});
