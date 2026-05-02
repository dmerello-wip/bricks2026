<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->dateTime('data')->nullable();
            $table->string('luogo')->nullable();
            $table->decimal('luogo_lat', 10, 7)->nullable();
            $table->decimal('luogo_lng', 10, 7)->nullable();
        });

        Schema::create('event_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'event');
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
        });

        Schema::create('event_slugs', function (Blueprint $table) {
            createDefaultSlugsTableFields($table, 'event');
        });

        Schema::create('event_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'event');
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_revisions');
        Schema::dropIfExists('event_translations');
        Schema::dropIfExists('event_slugs');
        Schema::dropIfExists('events');
    }
};
