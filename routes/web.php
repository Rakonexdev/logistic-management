<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SfqController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Notification Center Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::post('/api/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/api/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/api/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notifications/trigger-demo', [NotificationController::class, 'triggerDemoNotification'])->name('notifications.trigger-demo');
});

use App\Http\Controllers\AsnController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesOrderController;

Route::middleware(['auth', 'role:end_user,sfq_user'])->group(function () {
    // Product Routes
    Route::get('products/template', [ProductController::class, 'downloadTemplate'])->name('products.template');
    Route::post('products/bulk-upload', [ProductController::class, 'bulkUpload'])->name('products.bulk-upload');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('products/stock-visibility', [ProductController::class, 'stockVisibility'])->name('products.stock-visibility');
    Route::resource('products', ProductController::class);
});

Route::middleware(['auth', 'role:end_user'])->group(function () {
    Route::get('/end-user/dashboard', [DashboardController::class, 'endUser']);

    // ASN Routes
    Route::get('asns/template', [AsnController::class, 'downloadTemplate'])->name('asns.template');
    Route::resource('asns', AsnController::class);

    // Sales Order Routes
    Route::get('sales-orders/template', [SalesOrderController::class, 'downloadTemplate'])->name('sales-orders.template');
    Route::post('sales-orders/stock-check', [SalesOrderController::class, 'checkStock'])->name('sales-orders.stock-check');
    Route::get('sales-orders/export', [SalesOrderController::class, 'export'])->name('sales-orders.export');
    Route::resource('sales-orders', SalesOrderController::class);
});

Route::middleware(['auth', 'role:sfq_user'])->group(function () {
    Route::get('/sfq-user/dashboard', [DashboardController::class, 'sfqUser']);

    // SFQ Operations routes
    Route::get('/sfq/grns', [SfqController::class, 'grnIndex'])->name('sfq.grns.index');
    Route::post('/sfq/grns/confirm', [SfqController::class, 'grnConfirm'])->name('sfq.grns.confirm');

    Route::get('/sfq/locations', [SfqController::class, 'locationIndex'])->name('sfq.locations.index');
    Route::post('/sfq/locations/transfer', [SfqController::class, 'locationTransfer'])->name('sfq.locations.transfer');

    Route::get('/sfq/fulfillment', [SfqController::class, 'fulfillmentIndex'])->name('sfq.fulfillment.index');
    Route::post('/sfq/fulfillment/update', [SfqController::class, 'fulfillmentUpdate'])->name('sfq.fulfillment.update');

    Route::get('/sfq/deliveries', [SfqController::class, 'deliveryIndex'])->name('sfq.deliveries.index');
    Route::post('/sfq/deliveries/assign', [SfqController::class, 'deliveryAssign'])->name('sfq.deliveries.assign');

    Route::get('/sfq/returns', [SfqController::class, 'returnsIndex'])->name('sfq.returns.index');
    Route::post('/sfq/returns/classify', [SfqController::class, 'returnsClassify'])->name('sfq.returns.classify');

    Route::get('/sfq/cheques', [SfqController::class, 'chequesIndex'])->name('sfq.cheques.index');
    Route::post('/sfq/cheques/submit', [SfqController::class, 'chequesSubmit'])->name('sfq.cheques.submit');

    Route::get('/sfq/reconciliation', [SfqController::class, 'reconciliationIndex'])->name('sfq.reconciliation.index');
    Route::post('/sfq/reconciliation/update', [SfqController::class, 'reconciliationUpdate'])->name('sfq.reconciliation.update');

    Route::get('/sfq/invoices', [SfqController::class, 'invoicesIndex'])->name('sfq.invoices.index');
    Route::post('/sfq/invoices/create', [SfqController::class, 'invoicesCreate'])->name('sfq.invoices.create');

    Route::get('/sfq/reports', [SfqController::class, 'reportsIndex'])->name('sfq.reports.index');
});
