<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_rental_option', function (Blueprint $table): void {
            $table->unsignedBigInteger('cost_cents')->nullable()->after('rental_option_id');
            $table->boolean('is_daily_cost')->nullable()->after('cost_cents');
        });
    }

    public function down(): void
    {
        Schema::table('car_rental_option', function (Blueprint $table): void {
            $table->dropColumn(['cost_cents', 'is_daily_cost']);
        });
    }
};
