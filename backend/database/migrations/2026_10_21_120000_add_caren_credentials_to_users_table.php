<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('partner_api_username')->nullable();
            $table->text('partner_api_password')->nullable();
            $table->unsignedBigInteger('partner_rental_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['partner_api_username', 'partner_api_password', 'partner_rental_id']);
        });
    }
};
