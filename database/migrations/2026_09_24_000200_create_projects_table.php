<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug', 191)->unique();
            $table->string('short_description', 300);
            $table->text('description');
            $table->text('long_description')->nullable();
            $table->string('category', 60);
            $table->string('project_type', 40);
            $table->string('role');
            $table->string('client')->nullable();
            $table->string('period', 60)->nullable();
            $table->string('status', 80)->nullable();
            $table->boolean('is_confidential')->default(false);

            $table->string('image_path')->nullable();
            $table->string('image_alt')->nullable();
            $table->string('og_image_path')->nullable();

            $table->json('tech_stack')->nullable();
            $table->json('highlights')->nullable();
            $table->json('responsibilities')->nullable();
            // {"heading": "…", "intro": "…", "layers": [{"layer": "…", "title": "…", "description": "…", "items": ["…"]}]}
            $table->json('architecture')->nullable();
            // [{"value": "98%", "label": "…", "note": "…"}] — verified figures only.
            $table->json('metrics')->nullable();

            $table->string('project_url')->nullable();
            $table->string('project_url_label', 60)->nullable();
            $table->string('github_url')->nullable();
            $table->longText('case_study_content')->nullable();

            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
            $table->index(['is_featured', 'is_published']);
            $table->index('category');
        });

        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt');
            $table->string('caption')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_images');
        Schema::dropIfExists('projects');
    }
};
