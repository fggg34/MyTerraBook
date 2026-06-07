<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('main_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('main_category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_search_filter')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['main_category_id', 'is_active']);
        });

        $now = now();

        DB::table('main_categories')->insert([
            [
                'name' => 'Car',
                'slug' => 'car',
                'description' => 'Passenger cars and 4×4s for everyday driving.',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Campervan',
                'slug' => 'campervan',
                'description' => 'Campervans and motorhomes for self-contained road trips.',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('main_categories');
    }
};
