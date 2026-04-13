<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_damage_markers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_unit_id')->constrained('car_units')->cascadeOnDelete();
            $table->string('diagram_key', 64)->default('default');
            $table->decimal('position_x', 5, 2)->comment('0-100 percent across diagram');
            $table->decimal('position_y', 5, 2)->comment('0-100 percent down diagram');
            $table->text('description');
            $table->string('icon_path')->nullable();
            $table->timestamp('marked_at')->useCurrent();
            $table->timestamps();

            $table->index(['car_unit_id', 'diagram_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_damage_markers');
    }
};
