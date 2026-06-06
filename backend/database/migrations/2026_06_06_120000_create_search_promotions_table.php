<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('kicker')->nullable();
            $table->string('title');
            $table->text('text')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_href')->nullable();
            $table->string('layout')->default('card');
            $table->string('context')->default('all');
            $table->unsignedSmallInteger('insert_after')->default(2);
            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_promotions');
    }
};
