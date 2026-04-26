<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('menuitems', function (Blueprint $table) {
            createDefaultTableFields($table);

            $table->foreignId('menu_id')
                ->constrained()
                ->onDelete('cascade');

            $table->integer('position')->unsigned()->nullable();
            $table->nestedSet();
        });

        Schema::create('menuitem_translations', function (Blueprint $table) {
            createDefaultTranslationsTableFields($table, 'menuitem');
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('menuitem_translations');
        Schema::dropIfExists('menuitems');
    }
};
