<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_fares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('from_days');
            $table->unsignedSmallInteger('to_days');
            $table->unsignedBigInteger('price_per_day_cents');
            $table->timestamps();

            $table->index(['car_id', 'price_type_id', 'from_days']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_fares');
    }
};
