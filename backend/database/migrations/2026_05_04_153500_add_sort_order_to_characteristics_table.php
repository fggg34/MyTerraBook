<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characteristics', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->nullable()->after('display_text');
        });

        $orderedIds = DB::table('characteristics')->orderBy('id')->pluck('id');
        foreach ($orderedIds as $index => $id) {
            DB::table('characteristics')
                ->where('id', $id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('characteristics', function (Blueprint $table): void {
            $table->dropColumn('sort_order');
        });
    }
};
