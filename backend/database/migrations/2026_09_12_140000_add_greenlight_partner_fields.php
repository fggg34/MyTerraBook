<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $table->string('external_provider', 32)->nullable()->after('integration_token');
            $table->string('external_vehicle_id', 64)->nullable()->after('external_provider');
            $table->index(['external_provider', 'external_vehicle_id']);
        });

        Schema::table('locations', function (Blueprint $table): void {
            $table->string('external_provider', 32)->nullable()->after('host_user_id');
            $table->string('external_id', 64)->nullable()->after('external_provider');
            $table->index(['external_provider', 'external_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('external_provider', 32)->nullable()->after('notes');
            $table->string('external_reference', 64)->nullable()->after('external_provider');
            $table->date('customer_date_of_birth')->nullable()->after('customer_country');
            $table->index(['external_provider', 'external_reference']);
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $table->dropIndex(['external_provider', 'external_vehicle_id']);
            $table->dropColumn(['external_provider', 'external_vehicle_id']);
        });

        Schema::table('locations', function (Blueprint $table): void {
            $table->dropIndex(['external_provider', 'external_id']);
            $table->dropColumn(['external_provider', 'external_id']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['external_provider', 'external_reference']);
            $table->dropColumn(['external_provider', 'external_reference', 'customer_date_of_birth']);
        });
    }
};
