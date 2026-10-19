<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('partner_provider', 64)->nullable()->after('integration_token');
            $table->string('partner_api_base_url', 2048)->nullable()->after('partner_provider');
            $table->text('partner_api_key')->nullable()->after('partner_api_base_url');
            $table->boolean('partner_api_enabled')->default(false)->after('partner_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'partner_provider',
                'partner_api_base_url',
                'partner_api_key',
                'partner_api_enabled',
            ]);
        });
    }
};
