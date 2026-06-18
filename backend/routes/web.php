<?php

use App\Http\Controllers\Admin\HomepageController as AdminHomepageController;
use App\Http\Controllers\Api\Admin\OrderCheckinPdfController;
use App\Http\Controllers\Api\Admin\OrderContractPdfController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\SpaShellController;
use Illuminate\Support\Facades\Route;

Route::get('/favicon.ico', [FaviconController::class, 'show']);
Route::get('/spa-shell', [SpaShellController::class, 'show'])->name('spa.shell');

// Filament admin lives at /admin (login: /admin/login). Root redirects there for convenience.
Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::middleware(['auth', 'admin'])->prefix('admin/homepage')->name('admin.homepage.')->group(function (): void {
    Route::get('/', fn () => redirect()->to(\App\Filament\Pages\SiteContentHub::getUrl(['tab' => 'home'])))->name('index');
    Route::post('/reorder', [AdminHomepageController::class, 'reorder'])->name('reorder');
    Route::get('/{section}', fn () => redirect()->to(\App\Filament\Pages\SiteContentHub::getUrl(['tab' => 'home'])))->name('edit');
    Route::put('/{section}', [AdminHomepageController::class, 'update'])->name('update');
    Route::post('/{section}/image', [AdminHomepageController::class, 'uploadImage'])->name('image');
});

Route::middleware('auth')->prefix('admin/impact-rent/orders')->group(function (): void {
    Route::get('{order}/contract.pdf', [OrderContractPdfController::class, 'show'])
        ->name('admin.orders.contract-pdf');
    Route::get('{order}/checkin.pdf', [OrderCheckinPdfController::class, 'show'])
        ->name('admin.orders.checkin-pdf');
});
