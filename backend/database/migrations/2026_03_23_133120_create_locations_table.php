<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pickup / dropoff points with independent hours and closed days (VikRentCar-style).
     * opening_hours: JSON e.g. {"mon":{"open":"08:00","close":"18:00"}, ...}
     * closed_days: JSON array of YYYY-MM-DD or weekday keys for recurring closures.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('phone', 32)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('allows_pickup')->default(true);
            $table->boolean('allows_dropoff')->default(true);
            $table->json('opening_hours')->nullable();
            $table->json('closed_days')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
