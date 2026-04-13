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
            // Short FK name: default Laravel name exceeds MySQL's 64-char identifier limit.
            $table->unsignedBigInteger('car_distinctive_feature_definition_id');
            $table->foreign('car_distinctive_feature_definition_id', 'cudv_feat_def_fk')
                ->references('id')
                ->on('car_distinctive_feature_definitions')
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
