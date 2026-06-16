<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            if (! Schema::hasColumn('cars', 'drive_type')) {
                $table->string('drive_type', 16)->nullable()->after('fuel_type');
            }
        });

        DB::table('cars')->whereNull('drive_type')->update(['drive_type' => 'fwd']);
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            if (Schema::hasColumn('cars', 'drive_type')) {
                $table->dropColumn('drive_type');
            }
        });
    }
};
