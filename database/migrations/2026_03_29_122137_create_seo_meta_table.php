<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('locale', 5)->default('es');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->json('open_graph')->nullable();
            $table->json('twitter_card')->nullable();
            $table->json('json_ld')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->unique(['entity_type', 'entity_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
    }
};