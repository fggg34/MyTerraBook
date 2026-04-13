<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('auto_confirm_order')->default(false);
            $table->string('charge_or_discount', 16)->default('none')->comment('charge|discount|none');
            $table->string('charge_discount_type', 16)->nullable()->comment('percentage|fixed');
            $table->unsignedBigInteger('charge_fixed_cents')->nullable();
            $table->unsignedInteger('charge_percent_bips')->nullable();
            $table->json('config')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
