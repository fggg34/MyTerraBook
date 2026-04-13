<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('location_closing_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->date('specific_date')->nullable();
            $table->unsignedTinyInteger('recurring_weekday')->nullable()->comment('0=Sunday .. 6=Saturday when set');
            $table->timestamps();

            $table->index(['location_id', 'specific_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_closing_days');
    }
};
