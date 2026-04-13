<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_rental_option', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_option_id')->constrained('rental_options')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['car_id', 'rental_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rental_option');
    }
};
