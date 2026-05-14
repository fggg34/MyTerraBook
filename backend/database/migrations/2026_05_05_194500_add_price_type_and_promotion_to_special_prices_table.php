<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('special_prices', function (Blueprint $table): void {
            $table->json('price_type_ids')->nullable()->after('vehicle_ids');
            $table->boolean('is_promotion')->default(false)->after('year');
        });
    }

    public function down(): void
    {
        Schema::table('special_prices', function (Blueprint $table): void {
            $table->dropColumn(['price_type_ids', 'is_promotion']);
        });
    }
};
