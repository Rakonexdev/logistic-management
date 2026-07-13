<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ProfileController;
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
});

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
