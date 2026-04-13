<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('out_of_hours_fees', function (Blueprint $table) {
            $table->id();
            $table->time('time_from');
            $table->time('time_to');
            $table->string('applies_to', 16)->comment('pickup|dropoff|both');
            $table->unsignedBigInteger('cost_cents');
            $table->unsignedBigInteger('max_combined_charge_cents')->nullable();
            $table->json('vehicle_ids')->nullable();
            $table->json('location_ids')->nullable();
            $table->json('weekday_filter')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('out_of_hours_fees');
    }
};
