<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\AdminRateController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Atrium\AtriumBookingController;
use App\Http\Controllers\Atrium\AtriumDashboardController;
use App\Http\Controllers\Atrium\AtriumPaymentController;
use App\Http\Controllers\Atrium\AtriumProfileController;
use App\Http\Controllers\Atrium\AtriumReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Collector\CollectorCollectionController;
use App\Http\Controllers\Collector\CollectorReportController;
use App\Http\Controllers\Collector\CollectorProfileController;
use App\Http\Controllers\Cemetery\CemeteryOccupantRecordController;
use App\Http\Controllers\Cemetery\CemeteryPaymentCollectionController;
use App\Http\Controllers\Cemetery\CemeteryDashboardController;
use App\Http\Controllers\Cemetery\CemeteryProfileController;
use App\Http\Controllers\Cemetery\CemeteryReportController;
use App\Http\Controllers\Cemetery\CemeteryServiceLogController;
use App\Http\Controllers\Cemetery\CemeteryTransactionController;
use App\Http\Controllers\Fishport\FishportDashboardController;
use App\Http\Controllers\Fishport\FishportLogController;
use App\Http\Controllers\Fishport\FishportPhoneUploadController;
use App\Http\Controllers\Fishport\FishportProfileController;
use App\Http\Controllers\Fishport\FishportReportController;
use App\Http\Controllers\Fishport\FishportSendPaymentController;
use App\Http\Controllers\Fishport\FishportVesselRegistryController;
use App\Http\Controllers\Market\MarketDashboardController;
use App\Http\Controllers\Market\MarketProfileController;
use App\Http\Controllers\Market\MarketReportController;
use App\Http\Controllers\Market\MarketSendPaymentController;
use App\Http\Controllers\Market\MarketStallController;
use App\Http\Controllers\Market\MarketTenantController;
use App\Http\Controllers\Market\MarketTransactionController;
use App\Http\Controllers\Shared\NotificationController;
use App\Http\Controllers\Terminal\TerminalDashboardController;
use App\Http\Controllers\Terminal\TerminalParkingController;
use App\Http\Controllers\Terminal\TerminalReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login')->name('login.store');
Route::get('/admin-login', [LoginController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin-login', [LoginController::class, 'adminLogin'])->middleware('throttle:admin-login')->name('admin.login.store');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/fishport/phone-upload/{token}', [FishportPhoneUploadController::class, 'show'])->name('fishport.phone_upload.show');
Route::post('/fishport/phone-upload/{token}', [FishportPhoneUploadController::class, 'upload'])->name('fishport.phone_upload.upload');

Route::middleware('auth')->group(function () {
    // Admin
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard/all', [AdminDashboardController::class, 'all'])->name('admin.dashboard.all');
        Route::get('/dashboard/departments/{department}', [AdminDashboardController::class, 'department'])->name('admin.dashboard.department');
        Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users');
        Route::post('/users', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->middleware('throttle:sensitive-actions')->name('admin.users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->middleware('throttle:destructive-actions')->name('admin.users.destroy');
        Route::post('/users/collector-assignments', [UserManagementController::class, 'assignCollector'])->name('admin.users.collector_assignments.store');
        Route::post('/users/collector-assignments/{collector}/generate-missed-notice', [UserManagementController::class, 'generateMissedPaymentNotice'])
            ->middleware('throttle:sensitive-actions')
            ->name('admin.users.collector_assignments.generate_missed_notice');
        Route::get('/roles', [RoleManagementController::class, 'index'])->name('admin.roles');
        Route::post('/roles', [RoleManagementController::class, 'storeRole'])->name('admin.roles.store');
        Route::put('/roles/{role}', [RoleManagementController::class, 'updateRole'])->name('admin.roles.update');
        Route::put('/roles/{role}/permissions', [RoleManagementController::class, 'updatePermissions'])->name('admin.roles.permissions.update');
        Route::post('/roles/assignments', [RoleManagementController::class, 'assignUser'])->name('admin.roles.assignments.store');
        Route::get('/rates', [AdminRateController::class, 'index'])->name('admin.rates');
        Route::put('/rates', [AdminRateController::class, 'update'])->name('admin.rates.update');
        Route::get('/profile', [AdminProfileController::class, 'show'])->name('admin.profile');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
        Route::get('/reports/csv', [AdminReportController::class, 'exportCsv'])->name('admin.reports.csv');
        Route::get('/reports', [AdminReportController::class, 'index'])->name('admin.reports');
        Route::get('/transactions/csv', [AdminTransactionController::class, 'exportCsv'])->name('admin.transactions.csv');
        Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('admin.transactions');
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
        Route::delete('/vessel-registry/{fishportVessel}', [FishportVesselRegistryController::class, 'destroy'])->middleware('throttle:destructive-actions')->name('fishport.vessel_registry.destroy');
        Route::post('/vessel-registry/phone-upload/start', [FishportPhoneUploadController::class, 'start'])->name('fishport.phone_upload.start');
        Route::get('/vessel-registry/phone-upload/{token}/status', [FishportPhoneUploadController::class, 'status'])->name('fishport.phone_upload.status');
        Route::get('/vessel-registry/phone-upload/{token}/file', [FishportPhoneUploadController::class, 'file'])->name('fishport.phone_upload.file');
        Route::get('/send-payment', [FishportSendPaymentController::class, 'index'])->name('fishport.send_payment');
        Route::post('/send-payment', [FishportSendPaymentController::class, 'store'])->middleware('throttle:sensitive-actions')->name('fishport.send_payment.store');
        Route::patch('/send-payment/items/{dispatchItem}/cancel', [FishportSendPaymentController::class, 'cancel'])->middleware('throttle:sensitive-actions')->name('fishport.send_payment.items.cancel');
        Route::patch('/send-payment/items/{dispatchItem}/approve', [FishportSendPaymentController::class, 'approve'])->middleware('throttle:approval-actions')->name('fishport.send_payment.items.approve');
        Route::patch('/send-payment/items/{dispatchItem}/reject', [FishportSendPaymentController::class, 'reject'])->middleware('throttle:approval-actions')->name('fishport.send_payment.items.reject');
        Route::get('/profile', [FishportProfileController::class, 'show'])->name('fishport.profile');
        Route::put('/profile', [FishportProfileController::class, 'update'])->name('fishport.profile.update');
        Route::get('/reports', [FishportReportController::class, 'index'])->name('fishport.reports');
        Route::get('/reports/preview', [FishportReportController::class, 'preview'])->name('fishport.reports.preview');
        Route::get('/reports/pdf', [FishportReportController::class, 'pdf'])->name('fishport.reports.pdf');
        Route::get('/reports/csv', [FishportReportController::class, 'csv'])->name('fishport.reports.csv');
        Route::get('/records', [FishportLogController::class, 'index'])->name('fishport.records');
        Route::post('/records', [FishportLogController::class, 'store'])->name('fishport.records.store');
        Route::get('/records/{fishportLog}/receipt', [FishportLogController::class, 'downloadReceipt'])->name('fishport.records.receipt');
        Route::get('/records/{fishportLog}/receipt/pdf', [FishportLogController::class, 'downloadReceiptPdf'])->name('fishport.records.receipt.pdf');
        Route::get('/records/{fishportLog}/edit', [FishportLogController::class, 'edit'])->name('fishport.records.edit');
        Route::put('/records/{fishportLog}', [FishportLogController::class, 'update'])->name('fishport.records.update');
        Route::patch('/records/{fishportLog}/mark-paid', [FishportLogController::class, 'markPaid'])->middleware('throttle:approval-actions')->name('fishport.records.mark_paid');
        Route::patch('/records/{fishportLog}/cancel-payment', [FishportLogController::class, 'cancelPayment'])->middleware('throttle:sensitive-actions')->name('fishport.records.cancel_payment');
        Route::delete('/records/{fishportLog}', [FishportLogController::class, 'destroy'])->middleware('throttle:destructive-actions')->name('fishport.records.destroy');
    });

    // Market
    Route::prefix('market')->middleware('area:market')->group(function () {
        Route::get('/dashboard', [MarketDashboardController::class, 'index'])->name('market.dashboard');
        Route::get('/vendors', [MarketTenantController::class, 'index'])->name('market.vendors');
        Route::get('/vendors/csv', [MarketTenantController::class, 'csv'])->name('market.vendors.csv');
        Route::get('/vendors/{marketTenant}', [MarketTenantController::class, 'edit'])->name('market.vendors.edit');
        Route::get('/vendors/{marketTenant}/final-notice/pdf', [MarketTenantController::class, 'finalNoticePdf'])->name('market.vendors.final_notice.pdf');
        Route::put('/vendors/{marketTenant}', [MarketTenantController::class, 'update'])->name('market.vendors.update');
        Route::get('/stalls', [MarketStallController::class, 'index'])->name('market.stalls');
        Route::get('/stalls/csv', [MarketStallController::class, 'csv'])->name('market.stalls.csv');
        Route::post('/stalls', [MarketStallController::class, 'store'])->name('market.stalls.store');
        Route::put('/stalls/{marketStall}', [MarketStallController::class, 'update'])->name('market.stalls.update');
        Route::delete('/stalls/{marketStall}', [MarketStallController::class, 'destroy'])->middleware('throttle:destructive-actions')->name('market.stalls.destroy');
        Route::post('/stalls/locations', [MarketStallController::class, 'storeLocation'])->name('market.stalls.locations.store');
        Route::post('/stalls/rates', [MarketStallController::class, 'storeLocationRate'])->name('market.stalls.rates.store');
        Route::get('/profile', [MarketProfileController::class, 'show'])->name('market.profile');
        Route::put('/profile', [MarketProfileController::class, 'update'])->name('market.profile.update');
        Route::get('/send-payment', [MarketSendPaymentController::class, 'index'])->name('market.send_payment');
        Route::get('/send-payment/due-tracker', [MarketSendPaymentController::class, 'dueTracker'])->name('market.send_payment.due_tracker');
        Route::post('/send-payment', [MarketSendPaymentController::class, 'store'])->name('market.send_payment.store');
        Route::patch('/send-payment/items/{dispatchItem}/cancel', [MarketSendPaymentController::class, 'cancel'])->name('market.send_payment.items.cancel');
        Route::patch('/send-payment/items/{dispatchItem}/approve', [MarketSendPaymentController::class, 'approve'])->middleware('throttle:approval-actions')->name('market.send_payment.items.approve');
        Route::patch('/send-payment/items/{dispatchItem}/reject', [MarketSendPaymentController::class, 'reject'])->middleware('throttle:approval-actions')->name('market.send_payment.items.reject');
        Route::get('/records', [MarketTransactionController::class, 'index'])->name('market.records');
        Route::get('/reports', [MarketReportController::class, 'index'])->name('market.reports');
        Route::get('/reports/preview', [MarketReportController::class, 'preview'])->name('market.reports.preview');
        Route::get('/reports/pdf', [MarketReportController::class, 'pdf'])->name('market.reports.pdf');
        Route::get('/reports/csv', [MarketReportController::class, 'csv'])->name('market.reports.csv');
    });

    // Cemetery
    Route::prefix('cemetery')->middleware('area:cemetery')->group(function () {
        Route::get('/dashboard', [CemeteryDashboardController::class, 'index'])->name('cemetery.dashboard');
        Route::get('/records', [CemeteryOccupantRecordController::class, 'index'])->name('cemetery.records');
        Route::get('/records/csv', [CemeteryOccupantRecordController::class, 'csv'])->name('cemetery.records.csv');
        Route::post('/records', [CemeteryOccupantRecordController::class, 'store'])->name('cemetery.records.store');
        Route::put('/records/{occupantRecord}', [CemeteryOccupantRecordController::class, 'update'])->name('cemetery.records.update');
        Route::delete('/records/{occupantRecord}', [CemeteryOccupantRecordController::class, 'destroy'])->middleware('throttle:destructive-actions')->name('cemetery.records.destroy');
        Route::get('/services', [CemeteryServiceLogController::class, 'index'])->name('cemetery.services');
        Route::get('/services/csv', [CemeteryServiceLogController::class, 'csv'])->name('cemetery.services.csv');
        Route::post('/services', [CemeteryServiceLogController::class, 'store'])->name('cemetery.services.store');
        Route::put('/services/{serviceLog}', [CemeteryServiceLogController::class, 'update'])->name('cemetery.services.update');
        Route::delete('/services/{serviceLog}', [CemeteryServiceLogController::class, 'destroy'])->middleware('throttle:destructive-actions')->name('cemetery.services.destroy');
        Route::get('/transactions', [CemeteryTransactionController::class, 'index'])->name('cemetery.transactions');
        Route::get('/transactions/csv', [CemeteryTransactionController::class, 'csv'])->name('cemetery.transactions.csv');
        Route::get('/transactions/{transaction}', [CemeteryTransactionController::class, 'show'])->name('cemetery.transactions.show');
        Route::post('/transactions', [CemeteryTransactionController::class, 'store'])->name('cemetery.transactions.store');
        Route::put('/transactions/{transaction}', [CemeteryTransactionController::class, 'update'])->name('cemetery.transactions.update');
        Route::delete('/transactions/{transaction}', [CemeteryTransactionController::class, 'destroy'])->middleware('throttle:destructive-actions')->name('cemetery.transactions.destroy');
        Route::get('/payments', [CemeteryPaymentCollectionController::class, 'index'])->name('cemetery.payments');
        Route::get('/payments/csv', [CemeteryPaymentCollectionController::class, 'csv'])->name('cemetery.payments.csv');
        Route::post('/payments', [CemeteryPaymentCollectionController::class, 'store'])->name('cemetery.payments.store');
        Route::post('/transactions/{transaction}/quick-pay', [CemeteryPaymentCollectionController::class, 'quickPay'])->name('cemetery.payments.quick_pay');
        Route::get('/payments/{paymentCollection}/receipt', [CemeteryPaymentCollectionController::class, 'receipt'])->name('cemetery.payments.receipt');
        Route::put('/payments/{paymentCollection}', [CemeteryPaymentCollectionController::class, 'update'])->name('cemetery.payments.update');
        Route::delete('/payments/{paymentCollection}', [CemeteryPaymentCollectionController::class, 'destroy'])->middleware('throttle:destructive-actions')->name('cemetery.payments.destroy');
        Route::get('/reports', [CemeteryReportController::class, 'index'])->name('cemetery.reports');
        Route::get('/reports/csv', [CemeteryReportController::class, 'csv'])->name('cemetery.reports.csv');
        Route::get('/reports/preview', [CemeteryReportController::class, 'preview'])->name('cemetery.reports.preview');
        Route::get('/reports/pdf', [CemeteryReportController::class, 'pdf'])->name('cemetery.reports.pdf');
        Route::get('/profile', [CemeteryProfileController::class, 'show'])->name('cemetery.profile');
        Route::put('/profile', [CemeteryProfileController::class, 'update'])->name('cemetery.profile.update');
    });

    // Terminal
    Route::prefix('terminal')->middleware('area:terminal')->group(function () {
        Route::get('/dashboard', [TerminalDashboardController::class, 'index'])->name('terminal.dashboard');

        Route::get('/records', [TerminalParkingController::class, 'index'])->name('terminal.records');
        Route::get('/reports', [TerminalReportController::class, 'index'])->name('terminal.reports');
        Route::get('/reports/preview', [TerminalReportController::class, 'preview'])->name('terminal.reports.preview');
        Route::get('/reports/pdf', [TerminalReportController::class, 'pdf'])->name('terminal.reports.pdf');
        Route::get('/reports/csv', [TerminalReportController::class, 'csv'])->name('terminal.reports.csv');
        Route::get('/send-payment', [TerminalParkingController::class, 'sendPayment'])->name('terminal.send_payment');
        Route::post('/payments/simple', [TerminalParkingController::class, 'storeSimplePayment'])->name('terminal.simple_payments.store');
        Route::put('/payments/simple/{quickPayment}', [TerminalParkingController::class, 'updateSimplePayment'])->name('terminal.simple_payments.update');
        Route::delete('/payments/simple/{quickPayment}', [TerminalParkingController::class, 'destroySimplePayment'])->middleware('throttle:destructive-actions')->name('terminal.simple_payments.destroy');
        Route::patch('/payments/simple/{quickPayment}/mark-paid', [TerminalParkingController::class, 'markSimplePaymentPaid'])->middleware('throttle:approval-actions')->name('terminal.simple_payments.mark_paid');
    });

    // Atrium
    Route::prefix('atrium')->middleware('area:atrium')->group(function () {
        Route::get('/dashboard', [AtriumDashboardController::class, 'index'])->name('atrium.dashboard');

        Route::get('/records', [AtriumBookingController::class, 'index'])->name('atrium.records');
        Route::redirect('/booking', '/atrium/bookings');
        Route::redirect('/calendar', '/atrium/bookings');

        Route::get('/bookings', [AtriumBookingController::class, 'index'])->name('atrium.bookings');
        Route::get('/bookings/create', [AtriumBookingController::class, 'create'])->name('atrium.bookings.create');
        Route::post('/bookings', [AtriumBookingController::class, 'store'])->name('atrium.bookings.store');
        Route::get('/bookings/{event}', [AtriumBookingController::class, 'show'])->name('atrium.bookings.show');
        Route::get('/bookings/{event}/edit', [AtriumBookingController::class, 'edit'])->name('atrium.bookings.edit');
        Route::put('/bookings/{event}', [AtriumBookingController::class, 'update'])->name('atrium.bookings.update');
        Route::delete('/bookings/{event}', [AtriumBookingController::class, 'destroy'])->middleware('throttle:destructive-actions')->name('atrium.bookings.destroy');
        Route::patch('/bookings/{event}/cancel', [AtriumBookingController::class, 'cancel'])->name('atrium.bookings.cancel');
        Route::patch('/bookings/{event}/complete', [AtriumBookingController::class, 'complete'])->name('atrium.bookings.complete');

        Route::get('/payments', [AtriumPaymentController::class, 'index'])->name('atrium.payments');
        Route::get('/payments/create', [AtriumPaymentController::class, 'create'])->name('atrium.payments.create');
        Route::post('/payments', [AtriumPaymentController::class, 'store'])->name('atrium.payments.store');
        Route::get('/payments/{payment}', [AtriumPaymentController::class, 'show'])->name('atrium.payments.show');
        Route::get('/payments/{payment}/edit', [AtriumPaymentController::class, 'edit'])->name('atrium.payments.edit');
        Route::put('/payments/{payment}', [AtriumPaymentController::class, 'update'])->name('atrium.payments.update');
        Route::delete('/payments/{payment}', [AtriumPaymentController::class, 'destroy'])->middleware('throttle:destructive-actions')->name('atrium.payments.destroy');

        Route::get('/reports', [AtriumReportController::class, 'index'])->name('atrium.reports');
        Route::get('/reports/preview', [AtriumReportController::class, 'preview'])->name('atrium.reports.preview');
        Route::get('/reports/pdf', [AtriumReportController::class, 'pdf'])->name('atrium.reports.pdf');
        Route::get('/reports/csv', [AtriumReportController::class, 'csv'])->name('atrium.reports.csv');
        Route::get('/profile', [AtriumProfileController::class, 'show'])->name('atrium.profile');
        Route::put('/profile', [AtriumProfileController::class, 'update'])->name('atrium.profile.update');
    });

    // Collector
    Route::prefix('collector')->middleware('area:collector')->group(function () {
        Route::get('/dashboard', [CollectorCollectionController::class, 'dashboard'])->name('collector.dashboard');
        Route::get('/pending-collections', [CollectorCollectionController::class, 'pendingCollections'])->name('collector.pending_collections');
        Route::post('/pending-collections/{dispatchItem}/collect', [CollectorCollectionController::class, 'collect'])->middleware('throttle:sensitive-actions')->name('collector.pending_collections.collect');
        Route::post('/payments/{dispatchItem}/cancel', [CollectorCollectionController::class, 'cancelAwaiting'])->middleware('throttle:sensitive-actions')->name('collector.payments.cancel');
        Route::get('/payments', [CollectorCollectionController::class, 'payments'])->name('collector.payments');
        Route::get('/reports', [CollectorReportController::class, 'index'])->name('collector.reports');
        Route::get('/reports/csv', [CollectorReportController::class, 'csv'])->name('collector.reports.csv');
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
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::get('/notifications/read-all', [NotificationController::class, 'readAllLink'])->name('notifications.read_all_link');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read_all');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'markReadLink'])->name('notifications.mark_read_link');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.mark_read');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
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
