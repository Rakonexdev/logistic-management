<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\ProductController;

Route::middleware(['auth', 'role:end_user'])->group(function () {
    Route::get('/end-user/dashboard', [DashboardController::class, 'endUser']);
    
    // Product Routes
    Route::get('products/template', [ProductController::class, 'downloadTemplate'])->name('products.template');
    Route::post('products/bulk-upload', [ProductController::class, 'bulkUpload'])->name('products.bulk-upload');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::resource('products', ProductController::class);
});

Route::middleware(['auth', 'role:sfq_user'])->group(function () {
    Route::get('/sfq-user/dashboard', [DashboardController::class, 'sfqUser']);
});
