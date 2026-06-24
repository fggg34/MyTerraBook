<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Support\BookingConfirmationUrl;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('confirmation_token', 64)->nullable()->unique()->after('reference');
            $table->string('confirmation_url', 512)->nullable()->after('confirmation_token');
        });

        Schema::table('guest_house_bookings', function (Blueprint $table) {
            $table->string('confirmation_token', 64)->nullable()->unique()->after('booking_reference');
            $table->string('confirmation_url', 512)->nullable()->after('confirmation_token');
        });

        foreach (\App\Models\Order::query()->whereNull('confirmation_token')->cursor() as $order) {
            BookingConfirmationUrl::assignToModel($order);
            $order->saveQuietly();
        }

        foreach (\App\Models\GuestHouseBooking::withTrashed()->whereNull('confirmation_token')->cursor() as $booking) {
            BookingConfirmationUrl::assignToModel($booking);
            $booking->saveQuietly();
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['confirmation_token', 'confirmation_url']);
        });

        Schema::table('guest_house_bookings', function (Blueprint $table) {
            $table->dropColumn(['confirmation_token', 'confirmation_url']);
        });
    }
};
