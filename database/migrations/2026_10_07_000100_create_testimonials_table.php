<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            // Optional: testimonials about a specific project appear on its case study.
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();

            // Nullable while a request link is waiting for the person to respond.
            $table->string('author_name', 120)->nullable();
            $table->string('author_title', 120)->nullable();
            $table->string('author_company', 120)->nullable();
            $table->string('relationship', 40)->nullable();
            // When false, only role/company are shown (for people who prefer not to be named).
            $table->boolean('show_name')->default(true);
            $table->text('quote')->nullable();
            $table->string('avatar_path')->nullable();
            // e.g. the matching LinkedIn recommendation, so visitors can verify it.
            $table->string('source_url')->nullable();

            // requested → received → published (or archived).
            $table->string('status', 20)->default('received');
            // Nothing is published without recorded permission.
            $table->timestamp('consent_at')->nullable();
            $table->string('consent_source', 20)->nullable();

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            // Private request links: only a SHA-256 hash of the token is stored.
            $table->string('request_token_hash', 64)->nullable()->unique();
            $table->timestamp('request_expires_at')->nullable();
            $table->string('request_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
