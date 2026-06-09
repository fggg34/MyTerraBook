<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $table->time('pickup_time_from')->nullable()->after('ical_import_url');
            $table->time('pickup_time_to')->nullable()->after('pickup_time_from');
            $table->time('dropoff_time_from')->nullable()->after('pickup_time_to');
            $table->time('dropoff_time_to')->nullable()->after('dropoff_time_from');
        });

        Schema::table('location_fees', function (Blueprint $table): void {
            $table->foreignId('car_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('location_fees', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('car_id');
        });

        Schema::table('cars', function (Blueprint $table): void {
            $table->dropColumn([
                'pickup_time_from',
                'pickup_time_to',
                'dropoff_time_from',
                'dropoff_time_to',
            ]);
        });
    }
};
