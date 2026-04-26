<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seo_default_translations', function (Blueprint $table) {
            $table->dropColumn('default_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('seo_default_translations', function (Blueprint $table) {
            $table->string('default_keywords', 255)->nullable();
        });
    }
};
