<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_options', function (Blueprint $table): void {
            $table->unsignedSmallInteger('min_rental_days')->nullable()->after('max_cost_cap_cents');
            $table->unsignedSmallInteger('max_rental_days')->nullable()->after('min_rental_days');
            $table->unsignedInteger('sort_order')->default(0)->after('image_path');
        });

        $orderedIds = DB::table('rental_options')->orderBy('id')->pluck('id');
        foreach ($orderedIds as $index => $id) {
            DB::table('rental_options')
                ->where('id', $id)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('rental_options', function (Blueprint $table): void {
            $table->dropColumn([
                'min_rental_days',
                'max_rental_days',
                'sort_order',
            ]);
        });
    }
};
