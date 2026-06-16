<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('currency', 3)->nullable()->after('locale');
        });

        $default = strtoupper((string) data_get(Setting::getValue('shop.currency', ['code' => 'EUR']), 'code', 'EUR'));
        if (! in_array($default, config('currencies.supported', ['EUR']), true)) {
            $default = 'EUR';
        }

        DB::table('users')
            ->where('role', 'host')
            ->whereNull('currency')
            ->update(['currency' => $default]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
