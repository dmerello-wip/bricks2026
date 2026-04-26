<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_defaults', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title', 200)->nullable();
        });

        Schema::create('seo_default_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'seo_default');
            $table->string('default_title', 60)->nullable();
            $table->text('default_description')->nullable();
            $table->string('default_keywords', 255)->nullable();
            $table->string('default_og_title', 60)->nullable();
            $table->text('default_og_description')->nullable();
        });

        Schema::create('seo_default_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'seo_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_default_revisions');
        Schema::dropIfExists('seo_default_translations');
        Schema::dropIfExists('seo_defaults');
    }
};
