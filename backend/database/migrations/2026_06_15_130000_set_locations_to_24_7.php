<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('locations')->update([
            'default_opening_time' => '00:00:00',
            'default_closing_time' => '23:59:00',
        ]);
    }

    public function down(): void
    {
        // Opening hours were location-specific before this migration; cannot restore safely.
    }
};
