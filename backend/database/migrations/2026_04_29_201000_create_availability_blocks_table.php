<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->string('source', 32)->default('manual_close');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->unsignedInteger('units_blocked')->default(1);
            $table->string('external_uid')->nullable();
            $table->string('external_calendar')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['car_id', 'starts_at', 'ends_at']);
            $table->index(['source', 'external_uid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_blocks');
    }
};
