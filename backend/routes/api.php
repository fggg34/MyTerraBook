<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\Admin\AdminOrdersCsvExportController;
use App\Http\Controllers\Api\Admin\AdminReportsController;
use App\Http\Controllers\Api\Admin\AdminStatsController;
use App\Http\Controllers\Api\Admin\CategoryApiController;
use App\Http\Controllers\Api\Admin\OrderCheckinPdfController;
use App\Http\Controllers\Api\Admin\OrderContractPdfController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\HomepageController;
use App\Http\Controllers\Api\MeOrderController;
use App\Http\Controllers\Api\MeOrderIcalController;
use App\Http\Controllers\Api\Admin\AdminGuestHouseBookingController;
use App\Http\Controllers\Api\Admin\AdminGuestHouseController;
use App\Http\Controllers\Api\Admin\GuestHouseBookingPdfController;
use App\Http\Controllers\Api\GuestHouseBookingController;
use App\Http\Controllers\Api\GuestHouseController;
use App\Http\Controllers\Api\GuestHouseQuoteController;
use App\Http\Controllers\Api\MeGuestHouseBookingController;
use App\Http\Controllers\Api\PublicOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;

Route::middleware('web')->group(function () {
    Route::get('/site-preview', function (Request $request) {
        if (config('app.site_preview_open')) {
            return response()->json(['preview_unlocked' => true]);
        }

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

Route::get('/homepage', [HomepageController::class, 'show']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/locations', [CatalogController::class, 'locations']);
Route::get('/booking-restrictions', [CatalogController::class, 'bookingRestrictions']);
Route::get('/cars', [CatalogController::class, 'cars']);
Route::get('/cars/{car}', [CatalogController::class, 'car']);
Route::get('/cars/{car}/availability-calendar', [CatalogController::class, 'availabilityCalendar']);

Route::post('/orders/quote', [PublicOrderController::class, 'quote']);
Route::post('/orders', [PublicOrderController::class, 'store']);

Route::prefix('guest-houses')->group(function () {
    Route::get('/', [GuestHouseController::class, 'index']);
    Route::post('/bookings', [GuestHouseBookingController::class, 'store']);
    Route::get('/{slug}', [GuestHouseController::class, 'show']);
    Route::get('/{slug}/availability', [GuestHouseController::class, 'availability']);
    Route::post('/{slug}/quote', [GuestHouseQuoteController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::get('/me/orders', [MeOrderController::class, 'index']);
    Route::get('/me/orders/{order}/calendar.ics', [MeOrderIcalController::class, 'show'])
        ->name('api.me.orders.ical');

    Route::get('/me/guest-house-bookings', [MeGuestHouseBookingController::class, 'index']);
    Route::get('/me/guest-house-bookings/{ref}', [MeGuestHouseBookingController::class, 'show']);
    Route::post('/me/guest-house-bookings/{ref}/cancel', [MeGuestHouseBookingController::class, 'cancel']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('stats', AdminStatsController::class)->name('api.admin.stats');
    Route::get('reports', AdminReportsController::class)->name('api.admin.reports');
    Route::get('orders/export.csv', AdminOrdersCsvExportController::class)->name('api.admin.orders.export-csv');
    Route::get('orders/{order}/contract.pdf', [OrderContractPdfController::class, 'show'])->name('api.admin.orders.contract-pdf');
    Route::get('orders/{order}/checkin.pdf', [OrderCheckinPdfController::class, 'show'])->name('api.admin.orders.checkin-pdf');
    Route::apiResource('categories', CategoryApiController::class)->names([
        'index' => 'api.admin.categories.index',
        'store' => 'api.admin.categories.store',
        'show' => 'api.admin.categories.show',
        'update' => 'api.admin.categories.update',
        'destroy' => 'api.admin.categories.destroy',
    ]);

    Route::get('guest-houses/stats', [AdminGuestHouseController::class, 'stats'])->name('api.admin.guest-houses.stats');
    Route::get('guest-houses', [AdminGuestHouseController::class, 'index'])->name('api.admin.guest-houses.index');
    Route::get('guest-house-bookings', [AdminGuestHouseBookingController::class, 'index'])->name('api.admin.guest-house-bookings.index');
    Route::patch('guest-house-bookings/{booking}/status', [AdminGuestHouseBookingController::class, 'updateStatus'])->name('api.admin.guest-house-bookings.status');
    Route::get('guest-house-bookings/{booking}/contract.pdf', [GuestHouseBookingPdfController::class, 'show'])->name('api.admin.guest-house-bookings.contract-pdf');
});
