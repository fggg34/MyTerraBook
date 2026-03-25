<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\Admin\CarController as AdminCarController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\ExtraController as AdminExtraController;
use App\Http\Controllers\Api\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Api\Admin\PricingRuleController as AdminPricingRuleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\MeBookingController;
use App\Http\Controllers\Api\PaymentController;
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
Route::post('/bookings/quote', [BookingController::class, 'quote']);
Route::post('/bookings', [BookingController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::get('/me/bookings', [MeBookingController::class, 'index']);
    Route::post('/bookings/{booking}/payments', [PaymentController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [DashboardController::class, 'index']);
    Route::apiResource('categories', AdminCategoryController::class);
    Route::apiResource('locations', AdminLocationController::class);
    Route::apiResource('cars', AdminCarController::class);
    Route::apiResource('extras', AdminExtraController::class);
    Route::apiResource('pricing', AdminPricingRuleController::class);
    Route::apiResource('coupons', AdminCouponController::class);
    Route::apiResource('bookings', AdminBookingController::class)->except(['update'])->names([
        'index' => 'admin.bookings.index',
        'store' => 'admin.bookings.store',
        'show' => 'admin.bookings.show',
        'destroy' => 'admin.bookings.destroy',
    ]);
    Route::patch('/bookings/{booking}', [AdminBookingController::class, 'update']);
});
