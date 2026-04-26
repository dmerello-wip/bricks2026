<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('title', 200)->nullable();
        });

        Schema::create('menu_revisions', function (Blueprint $table) {
            createDefaultRevisionsTableFields($table, 'menu');
        });
    }

    public function down()
    {
        Schema::dropIfExists('menu_revisions');
        Schema::dropIfExists('menus');
    }
};
