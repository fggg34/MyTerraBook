<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\Admin\AdminOrdersCsvExportController;
use App\Http\Controllers\Api\Admin\AdminReportsController;
use App\Http\Controllers\Api\Admin\AdminStatsController;
use App\Http\Controllers\Api\Admin\CategoryApiController;
use App\Http\Controllers\Api\Admin\OrderCheckinPdfController;
use App\Http\Controllers\Api\Admin\OrderContractPdfController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\HomepageController;
use App\Http\Controllers\Api\SitePageController;
use App\Http\Controllers\Api\MeOrderController;
use App\Http\Controllers\Api\MeOrderIcalController;
use App\Http\Controllers\Api\Admin\AdminGuestHouseBookingController;
use App\Http\Controllers\Api\Admin\AdminGuestHouseController;
use App\Http\Controllers\Api\Admin\GuestHouseBookingPdfController;
use App\Http\Controllers\Api\GuestHouseBookingController;
use App\Http\Controllers\Api\GuestHouseController;
use App\Http\Controllers\Api\ListingReviewController;
use App\Http\Controllers\Api\GuestHouseQuoteController;
use App\Http\Controllers\Api\Host\HostBookingController;
use App\Http\Controllers\Api\Host\HostCarController;
use App\Http\Controllers\Api\Host\HostCatalogController;
use App\Http\Controllers\Api\Host\HostDashboardController;
use App\Http\Controllers\Api\Host\HostGuestHouseController;
use App\Http\Controllers\Api\MeGuestHouseBookingController;
use App\Http\Controllers\Api\PublicOrderController;
use App\Http\Controllers\Api\SearchPromotionController;
use App\Http\Controllers\Api\SearchSuggestionsController;
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
Route::get('/site-pages/{slug}', [SitePageController::class, 'show']);
Route::get('/blog-posts', [BlogPostController::class, 'index'])->name('api.blog-posts.index');
Route::get('/blog-posts/{slug}', [BlogPostController::class, 'show'])->name('api.blog-posts.show');
Route::post('/contact', [ContactController::class, 'store']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register-host', [AuthController::class, 'registerHost']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/locations', [CatalogController::class, 'locations']);
Route::get('/search/suggestions', [SearchSuggestionsController::class, 'index']);
Route::get('/search-promotions', [SearchPromotionController::class, 'index']);
Route::get('/booking-restrictions', [CatalogController::class, 'bookingRestrictions']);
Route::get('/cars', [CatalogController::class, 'cars']);
Route::get('/cars/{car}', [CatalogController::class, 'car']);
Route::get('/cars/{car}/reviews', [ListingReviewController::class, 'indexCar']);
Route::post('/cars/{car}/reviews', [ListingReviewController::class, 'storeCar']);
Route::get('/cars/{car}/availability-calendar', [CatalogController::class, 'availabilityCalendar']);

Route::post('/orders/quote', [PublicOrderController::class, 'quote']);
Route::post('/orders', [PublicOrderController::class, 'store']);

Route::prefix('guest-houses')->group(function () {
    Route::get('/', [GuestHouseController::class, 'index']);
    Route::post('/bookings', [GuestHouseBookingController::class, 'store']);
    Route::get('/{slug}', [GuestHouseController::class, 'show']);
    Route::get('/{slug}/reviews', [ListingReviewController::class, 'indexGuestHouse']);
    Route::post('/{slug}/reviews', [ListingReviewController::class, 'storeGuestHouse']);
    Route::get('/{slug}/availability', [GuestHouseController::class, 'availability']);
    Route::post('/{slug}/quote', [GuestHouseQuoteController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/host/apply', [AuthController::class, 'applyAsHost']);
    Route::get('/me/orders', [MeOrderController::class, 'index']);
    Route::get('/me/orders/{order}/calendar.ics', [MeOrderIcalController::class, 'show'])
        ->name('api.me.orders.ical');

    Route::get('/me/guest-house-bookings', [MeGuestHouseBookingController::class, 'index']);
    Route::get('/me/guest-house-bookings/{ref}', [MeGuestHouseBookingController::class, 'show']);
    Route::post('/me/guest-house-bookings/{ref}/cancel', [MeGuestHouseBookingController::class, 'cancel']);
});

Route::middleware(['auth:sanctum', 'host'])->prefix('host')->group(function () {
    Route::get('dashboard', [HostDashboardController::class, 'show']);

    Route::prefix('catalog')->group(function () {
        Route::get('categories', [HostCatalogController::class, 'categories']);
        Route::get('locations', [HostCatalogController::class, 'locations']);
        Route::get('characteristics', [HostCatalogController::class, 'characteristics']);
        Route::get('rental-options', [HostCatalogController::class, 'rentalOptions']);
        Route::get('price-types', [HostCatalogController::class, 'priceTypes']);
        Route::get('amenities', [HostCatalogController::class, 'amenities']);
        Route::get('tax-rates', [HostCatalogController::class, 'taxRates']);
    });

    Route::get('guest-houses', [HostGuestHouseController::class, 'index']);
    Route::post('guest-houses', [HostGuestHouseController::class, 'store']);
    Route::get('guest-houses/{guestHouse}', [HostGuestHouseController::class, 'show']);
    Route::patch('guest-houses/{guestHouse}', [HostGuestHouseController::class, 'update']);
    Route::delete('guest-houses/{guestHouse}', [HostGuestHouseController::class, 'destroy']);
    Route::post('guest-houses/{guestHouse}/submit', [HostGuestHouseController::class, 'submit']);
    Route::post('guest-houses/{guestHouse}/images', [HostGuestHouseController::class, 'uploadImages']);
    Route::delete('guest-houses/{guestHouse}/images/{image}', [HostGuestHouseController::class, 'deleteImage']);
    Route::get('guest-houses/{guestHouse}/availability-blocks', [HostGuestHouseController::class, 'availabilityBlocks']);
    Route::post('guest-houses/{guestHouse}/availability-blocks', [HostGuestHouseController::class, 'storeAvailabilityBlock']);
    Route::delete('guest-houses/{guestHouse}/availability-blocks/{block}', [HostGuestHouseController::class, 'destroyAvailabilityBlock']);

    Route::get('cars', [HostCarController::class, 'index']);
    Route::post('cars', [HostCarController::class, 'store']);
    Route::get('cars/{car}', [HostCarController::class, 'show']);
    Route::patch('cars/{car}', [HostCarController::class, 'update']);
    Route::delete('cars/{car}', [HostCarController::class, 'destroy']);
    Route::post('cars/{car}/submit', [HostCarController::class, 'submit']);
    Route::post('cars/{car}/images', [HostCarController::class, 'uploadImages']);
    Route::patch('cars/{car}/relations', [HostCarController::class, 'syncRelationsEndpoint']);
    Route::get('cars/{car}/units', [HostCarController::class, 'units']);
    Route::post('cars/{car}/units', [HostCarController::class, 'storeUnit']);
    Route::patch('cars/{car}/units/{unitId}', [HostCarController::class, 'updateUnit']);
    Route::delete('cars/{car}/units/{unitId}', [HostCarController::class, 'destroyUnit']);
    Route::get('cars/{car}/daily-fares', [HostCarController::class, 'dailyFares']);
    Route::post('cars/{car}/daily-fares', [HostCarController::class, 'storeDailyFare']);
    Route::patch('cars/{car}/daily-fares/{dailyFare}', [HostCarController::class, 'updateDailyFare']);
    Route::delete('cars/{car}/daily-fares/{dailyFare}', [HostCarController::class, 'destroyDailyFare']);
    Route::get('cars/{car}/hourly-fares', [HostCarController::class, 'hourlyFares']);
    Route::post('cars/{car}/hourly-fares', [HostCarController::class, 'storeHourlyFare']);
    Route::delete('cars/{car}/hourly-fares/{hourlyFare}', [HostCarController::class, 'destroyHourlyFare']);
    Route::get('cars/{car}/extra-hour-fares', [HostCarController::class, 'extraHourFares']);
    Route::post('cars/{car}/extra-hour-fares', [HostCarController::class, 'storeExtraHourFare']);
    Route::delete('cars/{car}/extra-hour-fares/{extraHourFare}', [HostCarController::class, 'destroyExtraHourFare']);
    Route::get('cars/{car}/availability-blocks', [HostCarController::class, 'availabilityBlocks']);
    Route::post('cars/{car}/availability-blocks', [HostCarController::class, 'storeAvailabilityBlock']);
    Route::delete('cars/{car}/availability-blocks/{block}', [HostCarController::class, 'destroyAvailabilityBlock']);
    Route::get('cars/{car}/special-prices', [HostCarController::class, 'specialPrices']);
    Route::post('cars/{car}/special-prices', [HostCarController::class, 'storeSpecialPrice']);
    Route::delete('cars/{car}/special-prices/{specialPrice}', [HostCarController::class, 'destroySpecialPrice']);

    Route::get('bookings/cars', [HostBookingController::class, 'carOrders']);
    Route::get('bookings/guest-houses', [HostBookingController::class, 'guestHouseBookings']);
    Route::patch('bookings/cars/{order}/status', [HostBookingController::class, 'updateCarOrderStatus']);
    Route::patch('bookings/guest-houses/{booking}/status', [HostBookingController::class, 'updateGuestHouseBookingStatus']);
    Route::get('bookings/cars/{order}/contract.pdf', [HostBookingController::class, 'carContractPdf']);
    Route::get('bookings/guest-houses/{booking}/contract.pdf', [HostBookingController::class, 'guestHouseContractPdf']);
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
