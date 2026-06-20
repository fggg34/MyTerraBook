<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_house_room_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_house_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('dim')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_house_room_details');
    }
};
