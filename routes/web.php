<?php

use App\Http\Controllers\TopupController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TopupController::class, 'index'])->name('home');
Route::get('/game/{slug}', [TopupController::class, 'show'])->name('topup.show');
Route::post('/topup/checkout', [TopupController::class, 'store'])->name('topup.store');
Route::get('/topup/status/{reference_id}', [TopupController::class, 'checkStatus'])->name('topup.status');
Route::get('/topup/status/{reference_id}/api', [TopupController::class, 'getAjaxStatus'])->name('topup.status.api');
Route::post('/topup/status/{reference_id}/refresh', [TopupController::class, 'refreshStatus'])->name('topup.status.refresh');
Route::post('/topup/check-id', [TopupController::class, 'validateId'])->name('topup.check-id');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session', 'auth'), // Fallback to 'auth' if jetstream config not present
    'verified',
])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Management
    Route::resource('/admin/categories', \App\Http\Controllers\Admin\CategoryController::class)->names('admin.categories');
    Route::resource('/admin/games', \App\Http\Controllers\Admin\GameController::class)->names('admin.games');
    Route::get('/admin/products/get-price-list', [\App\Http\Controllers\Admin\ProductController::class, 'getPriceList'])->name('admin.products.get-price-list');
    Route::post('/admin/products/import', [\App\Http\Controllers\Admin\ProductController::class, 'import'])->name('admin.products.import');
    Route::resource('/admin/products', \App\Http\Controllers\Admin\ProductController::class)->names('admin.products');
    Route::post('/admin/products/sync', [\App\Http\Controllers\Admin\ProductController::class, 'sync'])->name('admin.products.sync');
    Route::get('/admin/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('admin.orders.index');
    Route::delete('/admin/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'destroy'])->name('admin.orders.destroy');
    Route::get('/admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');
    
    // Landing Page Management
    Route::get('/admin/landing-page', [\App\Http\Controllers\Admin\LandingPageController::class, 'index'])->name('admin.landing-page.index');
    Route::post('/admin/landing-page/banners', [\App\Http\Controllers\Admin\LandingPageController::class, 'storeBanner'])->name('admin.landing-page.banners.store');
    Route::delete('/admin/landing-page/banners/{banner}', [\App\Http\Controllers\Admin\LandingPageController::class, 'deleteBanner'])->name('admin.landing-page.banners.destroy');
    Route::post('/admin/landing-page/popular', [\App\Http\Controllers\Admin\LandingPageController::class, 'updatePopularGames'])->name('admin.landing-page.popular.update');
    Route::post('/admin/landing-page/settings', [\App\Http\Controllers\Admin\LandingPageController::class, 'updateSettings'])->name('admin.landing-page.settings.update');
    
    // Payments
    Route::resource('/admin/payments', \App\Http\Controllers\Admin\PaymentMethodController::class)->names('admin.payments');
    Route::post('/admin/payments/{payment}/toggle', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'toggle'])->name('admin.payments.toggle');
});

require __DIR__.'/settings.php';
// require __DIR__.'/auth.php';
