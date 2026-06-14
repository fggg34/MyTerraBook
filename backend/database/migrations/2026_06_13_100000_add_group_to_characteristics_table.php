<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characteristics', function (Blueprint $table) {
            if (! Schema::hasColumn('characteristics', 'group')) {
                $table->string('group')->nullable()->after('display_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('characteristics', function (Blueprint $table) {
            if (Schema::hasColumn('characteristics', 'group')) {
                $table->dropColumn('group');
            }
        });
    }
};
