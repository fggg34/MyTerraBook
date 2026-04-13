<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_restrictions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->date('date_from');
            $table->date('date_to');
            $table->unsignedSmallInteger('min_rental_days')->nullable();
            $table->unsignedSmallInteger('max_rental_days')->nullable();
            $table->json('cta_weekdays')->nullable()->comment('Closed to arrival');
            $table->json('ctd_weekdays')->nullable()->comment('Closed to departure');
            $table->json('forced_pickup_weekdays')->nullable();
            $table->unsignedTinyInteger('min_length_multiplier')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['date_from', 'date_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_restrictions');
    }
};
