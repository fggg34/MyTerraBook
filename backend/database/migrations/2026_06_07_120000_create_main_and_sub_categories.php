<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $campervanCategoryNames = ['Van', 'SUV'];

    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        if (! Schema::hasTable('main_categories')) {
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
        }

        if (! Schema::hasTable('sub_categories')) {
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
        }

        $now = now();

        if (DB::table('main_categories')->count() === 0) {
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

        $carMainId = (int) DB::table('main_categories')->where('slug', 'car')->value('id');
        $campervanMainId = (int) DB::table('main_categories')->where('slug', 'campervan')->value('id');

        $legacyCategoryMap = [];

        foreach (DB::table('categories')->orderBy('sort_order')->get() as $legacyCategory) {
            $mainCategoryId = in_array($legacyCategory->name, $this->campervanCategoryNames, true)
                ? $campervanMainId
                : $carMainId;

            $existing = DB::table('sub_categories')->where('slug', $legacyCategory->slug)->first();
            if ($existing) {
                $legacyCategoryMap[(int) $legacyCategory->id] = (int) $existing->id;

                continue;
            }

            $subCategoryId = DB::table('sub_categories')->insertGetId([
                'main_category_id' => $mainCategoryId,
                'name' => $legacyCategory->name,
                'slug' => $legacyCategory->slug,
                'description' => $legacyCategory->description,
                'sort_order' => $legacyCategory->sort_order,
                'is_active' => $legacyCategory->is_active,
                'is_search_filter' => true,
                'created_at' => $legacyCategory->created_at ?? $now,
                'updated_at' => $legacyCategory->updated_at ?? $now,
                'deleted_at' => $legacyCategory->deleted_at,
            ]);

            $legacyCategoryMap[(int) $legacyCategory->id] = $subCategoryId;
        }

        if (! Schema::hasColumn('cars', 'sub_category_id')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->foreignId('sub_category_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            });
        }

        if ($legacyCategoryMap !== []) {
            foreach (DB::table('cars')->select(['id', 'category_id'])->get() as $car) {
                $subCategoryId = $legacyCategoryMap[(int) $car->category_id] ?? null;
                if ($subCategoryId !== null) {
                    DB::table('cars')->where('id', $car->id)->update(['sub_category_id' => $subCategoryId]);
                }
            }
        }

        $this->dropLegacyCategoryColumn();

        Schema::dropIfExists('categories');
    }

    public function down(): void
    {
        if (! Schema::hasTable('sub_categories')) {
            return;
        }

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        if (! Schema::hasColumn('cars', 'category_id')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->foreignId('category_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        foreach (DB::table('sub_categories')->orderBy('sort_order')->get() as $subCategory) {
            $categoryId = DB::table('categories')->insertGetId([
                'name' => $subCategory->name,
                'slug' => $subCategory->slug,
                'description' => $subCategory->description,
                'sort_order' => $subCategory->sort_order,
                'is_active' => $subCategory->is_active,
                'created_at' => $subCategory->created_at,
                'updated_at' => $subCategory->updated_at,
                'deleted_at' => $subCategory->deleted_at,
            ]);

            DB::table('cars')
                ->where('sub_category_id', $subCategory->id)
                ->update(['category_id' => $categoryId]);
        }

        if (Schema::hasColumn('cars', 'sub_category_id')) {
            Schema::table('cars', function (Blueprint $table) {
                if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                    $table->dropForeign(['sub_category_id']);
                }
                $table->dropIndex(['sub_category_id', 'is_active']);
                $table->dropColumn('sub_category_id');
            });
        }

        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('main_categories');
    }

    private function dropLegacyCategoryColumn(): void
    {
        if (! Schema::hasColumn('cars', 'category_id')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            $columns = collect(Schema::getColumnListing('cars'))
                ->reject(fn (string $column): bool => $column === 'category_id')
                ->values();

            $columnSql = $columns->map(fn (string $column) => "\"{$column}\"")->implode(', ');
            DB::statement("CREATE TABLE cars__category_migration AS SELECT {$columnSql} FROM cars");
            Schema::drop('cars');
            Schema::rename('cars__category_migration', 'cars');

            Schema::enableForeignKeyConstraints();

            return;
        }

        Schema::table('cars', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropColumn('category_id');
        });
    }
};
