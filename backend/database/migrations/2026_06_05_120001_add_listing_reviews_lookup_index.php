<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Live fix: composite index name exceeded MySQL's 64-char limit on the first migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('listing_reviews')) {
            Schema::create('listing_reviews', function (Blueprint $table) {
                $table->id();
                $table->morphs('reviewable');
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('guest_name', 80);
                $table->unsignedTinyInteger('rating');
                $table->text('body');
                $table->string('photo_path')->nullable();
                $table->boolean('is_approved')->default(true);
                $table->timestamps();

                $table->index(
                    ['reviewable_type', 'reviewable_id', 'is_approved', 'created_at'],
                    'lr_reviewable_approved_idx',
                );
            });

            return;
        }

        if ($this->indexExists('listing_reviews', 'lr_reviewable_approved_idx')) {
            return;
        }

        Schema::table('listing_reviews', function (Blueprint $table) {
            $table->index(
                ['reviewable_type', 'reviewable_id', 'is_approved', 'created_at'],
                'lr_reviewable_approved_idx',
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('listing_reviews')) {
            return;
        }

        if ($this->indexExists('listing_reviews', 'lr_reviewable_approved_idx')) {
            Schema::table('listing_reviews', function (Blueprint $table) {
                $table->dropIndex('lr_reviewable_approved_idx');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        $rows = $connection->select(
            'SELECT 1 FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?
             LIMIT 1',
            [$database, $table, $indexName],
        );

        return count($rows) > 0;
    }
};
