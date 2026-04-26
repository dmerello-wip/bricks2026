<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menuitems', function (Blueprint $table) {

            $table->string('type', 50)->nullable()->after('parent_id');

            $table->string('external_url', 2048)->nullable()->after('type');

            $table->string('target', 20)->nullable()->after('external_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menuitems', function (Blueprint $table) {
            $table->dropColumn(['type', 'external_url', 'target']);
        });
    }
};
