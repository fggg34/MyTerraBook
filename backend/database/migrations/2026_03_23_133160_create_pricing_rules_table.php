<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supports default daily/hourly rates, seasonal windows, location fees, and min duration rules.
     * adjustment: set = replace computed unit price; multiply = factor on base; add = surcharge per unit.
     */
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('rule_kind', 32);
            $table->foreignId('car_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->string('time_unit', 8)->default('day');
            $table->decimal('amount', 12, 4);
            $table->string('adjustment', 16)->default('set');
            $table->unsignedSmallInteger('priority')->default(0);
            $table->unsignedSmallInteger('min_duration_days')->nullable();
            $table->unsignedSmallInteger('min_duration_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['car_id', 'is_active']);
            $table->index(['date_from', 'date_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
