<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hourly_fares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('min_minutes');
            $table->unsignedSmallInteger('max_minutes');
            $table->unsignedBigInteger('total_price_cents')->comment('Total rental price for any duration in this window');
            $table->timestamps();

            $table->index(['car_id', 'price_type_id', 'min_minutes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hourly_fares');
    }
};
