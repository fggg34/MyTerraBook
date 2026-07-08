<?php

use App\Http\Controllers\Admin\AdminCalendarEmbedController;
use App\Http\Controllers\Admin\HomepageController as AdminHomepageController;
use App\Http\Controllers\Api\Admin\OrderCheckinPdfController;
use App\Http\Controllers\Api\Admin\OrderContractPdfController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\SpaShellController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::get('/favicon.ico', [FaviconController::class, 'show']);

Route::middleware(['auth', 'admin'])->get('/calendar-embed', AdminCalendarEmbedController::class)
    ->name('admin.calendar.embed');

// Legacy path kept for older iframe URLs.
Route::middleware(['auth', 'admin'])->get('/admin/embed/calendar', AdminCalendarEmbedController::class)
    ->name('admin.calendar.embed.legacy');

Route::get('/spa-shell', [SpaShellController::class, 'show'])
    ->name('spa.shell')
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        ValidateCsrfToken::class,
    ]);

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

Route::middleware('auth')->group(function (): void {
    Route::redirect('/admin/impact-rent/settings', '/admin/global-configuration');
    Route::redirect('/admin/impact-rent/payment-methods', '/admin/payment-methods');
    Route::redirect('/admin/impact-rent/custom-fields', '/admin/custom-fields');
});

Route::middleware('auth')->prefix('admin/impact-rent/orders')->group(function (): void {
    Route::get('{order}/contract.pdf', [OrderContractPdfController::class, 'show'])
        ->name('admin.orders.contract-pdf');
    Route::get('{order}/checkin.pdf', [OrderCheckinPdfController::class, 'show'])
        ->name('admin.orders.checkin-pdf');
});
