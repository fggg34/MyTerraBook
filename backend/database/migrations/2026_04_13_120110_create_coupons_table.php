<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type', 16)->comment('permanent|gift');
            $table->string('discount_type', 16)->comment('fixed|percentage');
            $table->unsignedBigInteger('discount_fixed_cents')->nullable();
            $table->unsignedInteger('discount_percent_bips')->nullable();
            $table->json('vehicle_ids')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->unsignedBigInteger('min_order_total_cents')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
