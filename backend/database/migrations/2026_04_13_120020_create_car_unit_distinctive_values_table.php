<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_unit_distinctive_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_unit_id')->constrained('car_units')->cascadeOnDelete();
            $table->foreignId('car_distinctive_feature_definition_id')
                ->constrained('car_distinctive_feature_definitions')
                ->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();

            $table->unique(['car_unit_id', 'car_distinctive_feature_definition_id'], 'unit_definition_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_unit_distinctive_values');
    }
};
