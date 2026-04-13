<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('dropoff_location_id')->constrained('locations')->cascadeOnDelete();
            $table->unsignedBigInteger('cost_cents');
            $table->boolean('multiply_by_days')->default(false);
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete();
            $table->boolean('apply_inverted')->default(false);
            $table->json('day_overrides')->nullable();
            $table->boolean('is_one_way_fee')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['pickup_location_id', 'dropoff_location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_fees');
    }
};
