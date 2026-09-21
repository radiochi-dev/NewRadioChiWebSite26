<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('music_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('platform')->default('soundcloud');
            $table->string('label_image_path')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->text('stream_url')->nullable();
            $table->text('external_url')->nullable();
            $table->string('genre')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
            $table->index(['position']);
        });

        Schema::create('music_track_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('music_track_id')->constrained('music_tracks')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('artist_name')->nullable();
            $table->string('title');
            $table->string('hero_title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('cta_primary_label')->nullable();
            $table->string('cta_secondary_label')->nullable();
            $table->timestamps();

            $table->unique(['music_track_id', 'locale']);
            $table->index(['locale']);
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('partner_type')->default('sponsor');
            $table->text('website_url')->nullable();
            $table->string('logo_path')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['partner_type', 'is_active']);
            $table->index(['position']);
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform');
            $table->string('label')->nullable();
            $table->text('url');
            $table->string('icon_key')->nullable();
            $table->string('location')->default('global');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['location', 'is_active']);
            $table->index(['position']);
        });

        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('document_type');
            $table->string('version')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'is_published']);
            $table->index(['position']);
        });

        Schema::create('legal_document_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained('legal_documents')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('cta_label')->nullable();
            $table->timestamps();

            $table->unique(['legal_document_id', 'locale']);
            $table->index(['locale']);
        });

        Schema::create('downloadable_files', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('disk')->default('public');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->text('external_url')->nullable();
            $table->string('collection')->nullable();
            $table->nullableMorphs('attachable');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['collection', 'is_active']);
            $table->index(['position']);
        });

        Schema::create('redirect_rules', function (Blueprint $table) {
            $table->id();
            $table->string('source_path')->unique();
            $table->text('destination_url');
            $table->unsignedSmallInteger('http_status')->default(301);
            $table->string('locale', 5)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->timestamps();

            $table->index(['locale', 'is_active']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key');
            $table->string('type')->default('string');
            $table->json('value')->nullable();
            $table->boolean('is_translatable')->default(false);
            $table->boolean('is_public')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['group', 'key']);
            $table->index(['group', 'is_public']);
            $table->index(['position']);
        });

        Schema::create('settings_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_id')->constrained('settings')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['setting_id', 'locale']);
            $table->index(['locale']);
        });

        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('integration');
            $table->string('event');
            $table->string('status');
            $table->string('direction')->nullable();
            $table->nullableMorphs('reference');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['integration', 'status']);
            $table->index(['event']);
            $table->index(['processed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
        Schema::dropIfExists('settings_translations');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('redirect_rules');
        Schema::dropIfExists('downloadable_files');
        Schema::dropIfExists('legal_document_translations');
        Schema::dropIfExists('legal_documents');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('music_track_translations');
        Schema::dropIfExists('music_tracks');
    }
};
