<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characteristics', function (Blueprint $table) {
            if (! Schema::hasColumn('characteristics', 'icon')) {
                $table->string('icon', 64)->nullable()->after('slug');
            }
        });

        Schema::table('rental_options', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_options', 'icon')) {
                $table->string('icon', 64)->nullable()->after('slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('characteristics', function (Blueprint $table) {
            if (Schema::hasColumn('characteristics', 'icon')) {
                $table->dropColumn('icon');
            }
        });

        Schema::table('rental_options', function (Blueprint $table) {
            if (Schema::hasColumn('rental_options', 'icon')) {
                $table->dropColumn('icon');
            }
        });
    }
};
