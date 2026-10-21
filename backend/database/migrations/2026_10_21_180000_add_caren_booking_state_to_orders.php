<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->uuid('partner_checkout_token')->nullable()->unique();
            $table->string('partner_sync_state', 40)->nullable()->index();
            $table->string('partner_sync_error')->nullable();
        });
        DB::table('price_types')->insertOrIgnore([
            'name' => 'Caren standard rate', 'slug' => 'caren-standard', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['partner_checkout_token']);
            $table->dropIndex(['partner_sync_state']);
            $table->dropColumn(['partner_checkout_token', 'partner_sync_state', 'partner_sync_error']);
        });
    }
};
