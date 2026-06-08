<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('category')->default('general');
            $table->string('audience')->default('customer');
            $table->boolean('is_enabled')->default(true);
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->string('heading')->nullable();
            $table->string('greeting')->nullable();
            $table->longText('body_html')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url_template')->nullable();
            $table->text('footer_note')->nullable();
            $table->json('available_variables')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
