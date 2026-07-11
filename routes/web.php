<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\AsnController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SalesOrderController;

Route::middleware(['auth', 'role:end_user'])->group(function () {
    Route::get('/end-user/dashboard', [DashboardController::class, 'endUser']);

    // Product Routes
    Route::get('products/template', [ProductController::class, 'downloadTemplate'])->name('products.template');
    Route::post('products/bulk-upload', [ProductController::class, 'bulkUpload'])->name('products.bulk-upload');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::resource('products', ProductController::class);

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
});
