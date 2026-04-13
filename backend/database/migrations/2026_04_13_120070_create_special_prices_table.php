<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_prices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->json('weekdays')->nullable()->comment('0=Sun .. 6=Sat');
            $table->string('type', 16)->comment('charge|discount');
            $table->string('value_mode', 16)->comment('percentage|fixed');
            $table->unsignedBigInteger('value_fixed_cents')->nullable();
            $table->unsignedInteger('value_percent_bips')->nullable()->comment('Percentage * 100, e.g. 1000 = 10%');
            $table->json('day_overrides')->nullable();
            $table->json('vehicle_ids')->nullable();
            $table->json('pickup_location_ids')->nullable();
            $table->json('dropoff_location_ids')->nullable();
            $table->boolean('apply_after_season_start')->default(false);
            $table->boolean('lock_first_day_rate')->default(false);
            $table->boolean('round_to_integer')->default(false);
            $table->unsignedSmallInteger('year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_prices');
    }
};
