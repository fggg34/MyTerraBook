<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_hour_fares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('charge_per_extra_hour_cents');
            $table->timestamps();

            $table->index(['car_id', 'price_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_hour_fares');
    }
};
