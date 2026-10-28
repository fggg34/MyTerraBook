<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('author_name')->nullable()->after('image_alt');
            $table->string('author_role')->nullable()->after('author_name');
            $table->string('author_photo')->nullable()->after('author_role');
            $table->json('tags')->nullable()->after('author_photo');
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'author_role', 'author_photo', 'tags']);
        });
    }
};
