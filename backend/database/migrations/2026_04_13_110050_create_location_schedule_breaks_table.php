<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_schedule_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_schedule_id')->constrained()->cascadeOnDelete();
            $table->time('break_start');
            $table->time('break_end');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_schedule_breaks');
    }
};
