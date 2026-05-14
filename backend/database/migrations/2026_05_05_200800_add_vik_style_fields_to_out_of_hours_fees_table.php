<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('out_of_hours_fees', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('id');
            $table->unsignedBigInteger('pickup_cost_cents')->nullable()->after('cost_cents');
            $table->unsignedBigInteger('dropoff_cost_cents')->nullable()->after('pickup_cost_cents');
            $table->unsignedBigInteger('tax_rate_id')->nullable()->after('max_combined_charge_cents');
        });
    }

    public function down(): void
    {
        Schema::table('out_of_hours_fees', function (Blueprint $table): void {
            $table->dropColumn([
                'name',
                'pickup_cost_cents',
                'dropoff_cost_cents',
                'tax_rate_id',
            ]);
        });
    }
};
