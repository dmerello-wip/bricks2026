<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            createDefaultTableFields($table);

            $table->string('band')->nullable();
            $table->unsignedInteger('nr_componenti')->nullable();
            $table->string('eta_media')->nullable();
            $table->string('citta')->nullable();
            $table->string('genere')->nullable();
            $table->unsignedInteger('durata')->nullable();
            $table->string('referente')->nullable();
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->string('video_file_path')->nullable();
            $table->string('video_link')->nullable();
            $table->boolean('privacy')->default(false);
            $table->string('evento')->nullable();
            $table->dateTime('data_iscrizione')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
