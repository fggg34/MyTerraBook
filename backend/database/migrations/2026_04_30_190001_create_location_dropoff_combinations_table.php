<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_dropoff_combinations', function (Blueprint $table) {
            $table->foreignId('pickup_location_id')
                ->constrained('locations')
                ->cascadeOnDelete();

            $table->foreignId('dropoff_location_id')
                ->constrained('locations')
                ->cascadeOnDelete();

            $table->primary(['pickup_location_id', 'dropoff_location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_dropoff_combinations');
    }
};
