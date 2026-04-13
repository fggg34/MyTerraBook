<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\Admin\AdminOrdersCsvExportController;
use App\Http\Controllers\Api\Admin\AdminStatsController;
use App\Http\Controllers\Api\Admin\CategoryApiController;
use App\Http\Controllers\Api\Admin\OrderContractPdfController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\MeOrderController;
use App\Http\Controllers\Api\MeOrderIcalController;
use App\Http\Controllers\Api\PublicOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;

Route::middleware('web')->group(function () {
    Route::get('/site-preview', function (Request $request) {
        $webUser = auth()->guard('web')->user();
        if ($webUser && $webUser->role === UserRole::Admin) {
            return response()->json(['preview_unlocked' => true]);
        }

        $token = $request->bearerToken();
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            $sanctumUser = $accessToken?->tokenable;
            if ($sanctumUser && $sanctumUser->role === UserRole::Admin) {
                return response()->json(['preview_unlocked' => true]);
            }
        }

        return response()->json(['preview_unlocked' => false]);
    });
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/locations', [CatalogController::class, 'locations']);
Route::get('/cars', [CatalogController::class, 'cars']);
Route::get('/cars/{car}', [CatalogController::class, 'car']);
Route::get('/cars/{car}/availability-calendar', [CatalogController::class, 'availabilityCalendar']);

Route::post('/orders/quote', [PublicOrderController::class, 'quote']);
Route::post('/orders', [PublicOrderController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::get('/me/orders', [MeOrderController::class, 'index']);
    Route::get('/me/orders/{order}/calendar.ics', [MeOrderIcalController::class, 'show'])
        ->name('api.me.orders.ical');
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('stats', AdminStatsController::class)->name('api.admin.stats');
    Route::get('orders/export.csv', AdminOrdersCsvExportController::class)->name('api.admin.orders.export-csv');
    Route::get('orders/{order}/contract.pdf', [OrderContractPdfController::class, 'show'])->name('api.admin.orders.contract-pdf');
    Route::apiResource('categories', CategoryApiController::class)->names([
        'index' => 'api.admin.categories.index',
        'store' => 'api.admin.categories.store',
        'show' => 'api.admin.categories.show',
        'update' => 'api.admin.categories.update',
        'destroy' => 'api.admin.categories.destroy',
    ]);
});
