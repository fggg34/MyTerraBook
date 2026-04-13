<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_location', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->boolean('allows_pickup')->default(true);
            $table->boolean('allows_dropoff')->default(true);
            $table->timestamps();

            $table->unique(['car_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_location');
    }
};
