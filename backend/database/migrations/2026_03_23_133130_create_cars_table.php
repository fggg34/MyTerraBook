<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('transmission', 32)->default('automatic');
            $table->string('fuel_type', 32)->default('petrol');
            $table->unsignedTinyInteger('seats')->default(5);
            $table->unsignedTinyInteger('bags')->default(2);
            $table->json('features')->nullable();
            $table->string('availability_status', 32)->default('available');
            $table->decimal('base_daily_price', 10, 2);
            $table->decimal('base_hourly_price', 10, 2)->nullable();
            $table->unsignedSmallInteger('min_rental_hours')->nullable();
            $table->unsignedSmallInteger('min_rental_days')->default(1);
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_id', 'is_active']);
            $table->index('availability_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
