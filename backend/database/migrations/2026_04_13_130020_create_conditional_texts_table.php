<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conditional_texts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content');
            $table->text('content_plain')->nullable();
            $table->json('conditions');
            $table->json('templates');
            $table->string('placement')->default('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conditional_texts');
    }
};
