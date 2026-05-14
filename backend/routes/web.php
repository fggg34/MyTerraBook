<?php

use App\Http\Controllers\Api\Admin\OrderCheckinPdfController;
use App\Http\Controllers\Api\Admin\OrderContractPdfController;
use Illuminate\Support\Facades\Route;

// Filament admin lives at /admin (login: /admin/login). Root redirects there for convenience.
Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::middleware('auth')->prefix('admin/impact-rent/orders')->group(function (): void {
    Route::get('{order}/contract.pdf', [OrderContractPdfController::class, 'show'])
        ->name('admin.orders.contract-pdf');
    Route::get('{order}/checkin.pdf', [OrderCheckinPdfController::class, 'show'])
        ->name('admin.orders.checkin-pdf');
});
