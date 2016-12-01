<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_roles', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('group_id');
            $table->string('name');
            $table->text('description');
            $table->string('code');
            $table->integer('sort_order')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_roles');
    }
}
