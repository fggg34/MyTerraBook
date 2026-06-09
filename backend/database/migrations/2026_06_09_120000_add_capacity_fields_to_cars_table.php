<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->unsignedTinyInteger('seats')->nullable()->after('fuel_type');
            $table->unsignedTinyInteger('sleeps')->nullable()->after('seats');
            $table->unsignedTinyInteger('bags')->nullable()->after('sleeps');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn(['seats', 'sleeps', 'bags']);
        });
    }
};
