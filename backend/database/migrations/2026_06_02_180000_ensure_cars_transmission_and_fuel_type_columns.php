<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            if (! Schema::hasColumn('cars', 'transmission')) {
                $table->string('transmission', 64)->nullable();
            }

            if (! Schema::hasColumn('cars', 'fuel_type')) {
                $table->string('fuel_type', 64)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('cars', 'transmission') ? 'transmission' : null,
                Schema::hasColumn('cars', 'fuel_type') ? 'fuel_type' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
