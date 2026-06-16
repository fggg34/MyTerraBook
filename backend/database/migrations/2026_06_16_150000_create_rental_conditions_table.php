<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('car_rental_condition', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->foreignId('rental_condition_id')->constrained('rental_conditions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['car_id', 'rental_condition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_rental_condition');
        Schema::dropIfExists('rental_conditions');
    }
};
