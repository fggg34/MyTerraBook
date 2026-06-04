<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_houses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('type');
            $table->string('status')->default('draft');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('max_guests')->default(2);
            $table->unsignedTinyInteger('bedrooms')->default(1);
            $table->unsignedTinyInteger('bathrooms')->default(1);
            $table->unsignedTinyInteger('beds')->default(1);
            $table->unsignedSmallInteger('min_nights')->default(1);
            $table->unsignedSmallInteger('max_nights')->nullable();
            $table->unsignedInteger('base_price_per_night');
            $table->unsignedInteger('cleaning_fee')->nullable();
            $table->unsignedInteger('security_deposit')->nullable();
            $table->time('check_in_time')->default('15:00:00');
            $table->time('check_out_time')->default('11:00:00');
            $table->string('cancellation_policy')->default('moderate');
            $table->string('thumbnail')->nullable();
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('guest_house_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_house_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('guest_house_amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('guest_house_amenity', function (Blueprint $table) {
            $table->foreignId('guest_house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained('guest_house_amenities')->cascadeOnDelete();
            $table->primary(['guest_house_id', 'amenity_id']);
        });

        Schema::create('guest_house_seasonal_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_house_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('date_from');
            $table->date('date_to');
            $table->unsignedInteger('price_per_night');
            $table->unsignedSmallInteger('minimum_nights')->nullable();
            $table->timestamps();
        });

        Schema::create('guest_house_availability_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_house_id')->constrained()->cascadeOnDelete();
            $table->date('blocked_from');
            $table->date('blocked_to');
            $table->string('reason');
            $table->text('note')->nullable();
            $table->string('source')->default('manual');
            $table->string('ical_uid')->nullable();
            $table->timestamps();
        });

        Schema::create('guest_house_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('booking_reference')->unique();
            $table->string('status')->default('pending');
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone')->nullable();
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('nights');
            $table->unsignedSmallInteger('guests_count');
            $table->unsignedInteger('base_total');
            $table->unsignedInteger('cleaning_fee')->default(0);
            $table->unsignedInteger('security_deposit')->default(0);
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('tax_amount')->default(0);
            $table->unsignedInteger('total_amount');
            $table->string('coupon_code')->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->text('special_requests')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('guest_house_booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_house_booking_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->string('method')->nullable();
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('guest_house_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_house_booking_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_house_reviews');
        Schema::dropIfExists('guest_house_booking_payments');
        Schema::dropIfExists('guest_house_bookings');
        Schema::dropIfExists('guest_house_availability_blocks');
        Schema::dropIfExists('guest_house_seasonal_prices');
        Schema::dropIfExists('guest_house_amenity');
        Schema::dropIfExists('guest_house_amenities');
        Schema::dropIfExists('guest_house_images');
        Schema::dropIfExists('guest_houses');
    }
};
