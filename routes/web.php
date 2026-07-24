<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriverAuthController;
use App\Http\Controllers\DriverDashboardController;
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
use App\Http\Controllers\DeliveryInstructionController;
use App\Http\Controllers\DeliveryInvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RentInvoiceController;
use App\Http\Controllers\ReturnInstructionController;
use App\Http\Controllers\SalesOrderController;

Route::middleware(['auth', 'role:end_user,sfq_user'])->group(function () {
    // Product Routes
    Route::get('products/template', [ProductController::class, 'downloadTemplate'])->name('products.template');
    Route::post('products/bulk-upload', [ProductController::class, 'bulkUpload'])->name('products.bulk-upload');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('products/stock-visibility', [ProductController::class, 'stockVisibility'])->name('products.stock-visibility');
    Route::resource('products', ProductController::class);

    // Delivery Invoice Routes
    Route::resource('delivery-invoices', DeliveryInvoiceController::class);

    // Rent Invoice Routes
    Route::post('rent-invoices/{id}/mark-paid', [RentInvoiceController::class, 'markPaid'])->name('rent-invoices.mark-paid');
    Route::resource('rent-invoices', RentInvoiceController::class);
});

Route::middleware(['auth', 'role:end_user'])->group(function () {
    Route::get('/end-user/dashboard', [DashboardController::class, 'endUser']);

    // ASN Routes
    Route::get('asns/template', [AsnController::class, 'downloadTemplate'])->name('asns.template');
    Route::resource('asns', AsnController::class, ['except' => ['show']]);

    // Sales Order Routes
    Route::get('sales-orders/template', [SalesOrderController::class, 'downloadTemplate'])->name('sales-orders.template');
    Route::post('sales-orders/stock-check', [SalesOrderController::class, 'checkStock'])->name('sales-orders.stock-check');
    Route::get('sales-orders/export', [SalesOrderController::class, 'export'])->name('sales-orders.export');
    Route::resource('sales-orders', SalesOrderController::class);

    // Delivery Instruction Routes
    Route::get('delivery-instructions/template', [DeliveryInstructionController::class, 'downloadTemplate'])->name('delivery-instructions.template');
    Route::get('delivery-instructions/{id}/fulfill-remaining', [DeliveryInstructionController::class, 'fulfillRemaining'])->name('delivery-instructions.fulfill-remaining');
    Route::post('delivery-notes/{id}/release', [DeliveryInstructionController::class, 'releaseDeliveryNote'])->name('delivery-notes.release');
    Route::get('delivery-notes', [DeliveryInstructionController::class, 'deliveryNotesIndex'])->name('delivery-notes.index');
    Route::resource('delivery-instructions', DeliveryInstructionController::class);

    // Return Instruction Routes
    Route::post('return-instructions/{id}/inspection', [ReturnInstructionController::class, 'updateInspection'])->name('return-instructions.inspection');
    Route::resource('return-instructions', ReturnInstructionController::class);
});

// Shared ASN, Delivery Note & Return Print Routes (placed after resource/static routes to avoid wildcard conflicts)
Route::middleware(['auth', 'role:end_user,sfq_user'])->group(function () {
    Route::get('asns/{asn}/report', [AsnController::class, 'generateReport'])->name('asns.report');
    Route::get('asns/{asn}', [AsnController::class, 'show'])->name('asns.show');
    Route::get('delivery-notes/{id}/print', [DeliveryInstructionController::class, 'printDeliveryNote'])->name('delivery-notes.print');
    Route::get('delivery-instructions/{id}/attachment', [DeliveryInstructionController::class, 'downloadAttachment'])->name('delivery-instructions.attachment');
    Route::get('delivery-invoices/{id}/print', [DeliveryInvoiceController::class, 'print'])->name('delivery-invoices.print');
    Route::get('rent-invoices/{id}/print', [RentInvoiceController::class, 'print'])->name('rent-invoices.print');
    Route::get('return-instructions/{id}/print', [ReturnInstructionController::class, 'print'])->name('return-instructions.print');
    Route::get('return-instructions/{id}/attachment', [ReturnInstructionController::class, 'downloadAttachment'])->name('return-instructions.attachment');
});

Route::middleware(['auth', 'role:sfq_user'])->group(function () {
    Route::get('/sfq-user/dashboard', [DashboardController::class, 'sfqUser']);

    // SFQ Operations routes
    Route::get('/sfq/grns', [SfqController::class, 'grnIndex'])->name('sfq.grns.index');
    Route::post('/sfq/grns/confirm', [SfqController::class, 'grnConfirm'])->name('sfq.grns.confirm');

    Route::get('/sfq/locations', [SfqController::class, 'locationIndex'])->name('sfq.locations.index');
    Route::get('/sfq/locations/create', [SfqController::class, 'locationCreate'])->name('sfq.locations.create');
    Route::post('/sfq/locations', [SfqController::class, 'locationStore'])->name('sfq.locations.store');
    Route::get('/sfq/locations/{id}/edit', [SfqController::class, 'locationEdit'])->name('sfq.locations.edit');
    Route::put('/sfq/locations/{id}', [SfqController::class, 'locationUpdate'])->name('sfq.locations.update');
    Route::delete('/sfq/locations/{id}', [SfqController::class, 'locationDestroy'])->name('sfq.locations.destroy');
    Route::post('/sfq/locations/transfer', [SfqController::class, 'locationTransfer'])->name('sfq.locations.transfer');

    Route::get('/sfq/fulfillment', [SfqController::class, 'fulfillmentIndex'])->name('sfq.fulfillment.index');
    Route::post('/sfq/fulfillment/update', [SfqController::class, 'fulfillmentUpdate'])->name('sfq.fulfillment.update');
    Route::post('/sfq/fulfillment/delivery-note', [SfqController::class, 'fulfillmentUpdateDeliveryNote'])->name('sfq.fulfillment.delivery-note');

    Route::get('/sfq/deliveries', [SfqController::class, 'deliveryIndex'])->name('sfq.deliveries.index');
    Route::post('/sfq/deliveries/assign', [SfqController::class, 'deliveryAssign'])->name('sfq.deliveries.assign');
    Route::post('/sfq/deliveries/complete', [SfqController::class, 'deliveryComplete'])->name('sfq.deliveries.complete');

    Route::get('/sfq/returns', [SfqController::class, 'returnsIndex'])->name('sfq.returns.index');
    Route::post('/sfq/returns/assign', [SfqController::class, 'returnsAssign'])->name('sfq.returns.assign');
    Route::post('/sfq/returns/status', [SfqController::class, 'returnsStatusUpdate'])->name('sfq.returns.status');
    Route::post('/sfq/returns/classify', [SfqController::class, 'returnsClassify'])->name('sfq.returns.classify');

    Route::get('/sfq/cheques', [SfqController::class, 'chequesIndex'])->name('sfq.cheques.index');
    Route::post('/sfq/cheques/submit', [SfqController::class, 'chequesSubmit'])->name('sfq.cheques.submit');

    Route::get('/sfq/reconciliation', [SfqController::class, 'reconciliationIndex'])->name('sfq.reconciliation.index');
    Route::post('/sfq/reconciliation/update', [SfqController::class, 'reconciliationUpdate'])->name('sfq.reconciliation.update');

    Route::get('/sfq/invoices', [SfqController::class, 'invoicesIndex'])->name('sfq.invoices.index');
    Route::post('/sfq/invoices/create', [SfqController::class, 'invoicesCreate'])->name('sfq.invoices.create');

    Route::get('/sfq/reports', [SfqController::class, 'reportsIndex'])->name('sfq.reports.index');
});

// Driver Mobile App Routes
Route::get('/driver/login', [DriverAuthController::class, 'showLoginForm'])->name('driver.login');
Route::post('/driver/login', [DriverAuthController::class, 'login']);
Route::post('/driver/logout', [DriverAuthController::class, 'logout'])->name('driver.logout');

Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/driver/dashboard', [DriverDashboardController::class, 'index'])->name('driver.dashboard');

    // Delivery Actions
    Route::post('/driver/deliveries/{id}/arrive', [DriverDashboardController::class, 'markArrived'])->name('driver.deliveries.arrive');
    Route::post('/driver/deliveries/{id}/complete', [DriverDashboardController::class, 'markDelivered'])->name('driver.deliveries.complete');
    Route::post('/driver/deliveries/{id}/issue', [DriverDashboardController::class, 'reportDeliveryIssue'])->name('driver.deliveries.issue');

    // Return Pickup Actions
    Route::post('/driver/returns/{returnPickup}/start', [DriverDashboardController::class, 'startPickup'])->name('driver.returns.start');
    Route::post('/driver/returns/{returnPickup}/complete', [DriverDashboardController::class, 'completePickup'])->name('driver.returns.complete');
    Route::post('/driver/returns/{returnPickup}/handover', [DriverDashboardController::class, 'submitHandover'])->name('driver.returns.handover');

    // Cheque Actions
    Route::post('/driver/cheques/{chequeCollection}/collect', [DriverDashboardController::class, 'collectCheque'])->name('driver.cheques.collect');
    Route::post('/driver/cheques/{chequeCollection}/submit', [DriverDashboardController::class, 'submitCheque'])->name('driver.cheques.submit');
    Route::post('/driver/cheques/{chequeCollection}/issue', [DriverDashboardController::class, 'reportChequeIssue'])->name('driver.cheques.issue');
});
