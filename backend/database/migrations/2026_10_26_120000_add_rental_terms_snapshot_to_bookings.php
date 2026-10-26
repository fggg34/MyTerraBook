<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->longText('rental_terms_html')->nullable()->after('notes');
        });

        Schema::table('guest_house_bookings', function (Blueprint $table) {
            $table->longText('rental_terms_html')->nullable()->after('special_requests');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('rental_terms_html');
        });

        Schema::table('guest_house_bookings', function (Blueprint $table) {
            $table->dropColumn('rental_terms_html');
        });
    }
};
