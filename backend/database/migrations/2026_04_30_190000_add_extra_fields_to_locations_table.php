<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('tax_rate_id')
                ->nullable()
                ->after('longitude')
                ->constrained('tax_rates')
                ->nullOnDelete();

            $table->text('description')
                ->nullable()
                ->after('tax_rate_id');

            $table->time('default_closing_time')
                ->nullable()
                ->after('default_opening_time');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tax_rate_id');
            $table->dropColumn(['description', 'default_closing_time']);
        });
    }
};
