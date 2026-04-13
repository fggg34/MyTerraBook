<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 32);
            $table->string('label');
            $table->bigInteger('amount_cents');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->json('meta')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['order_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_line_items');
    }
};
