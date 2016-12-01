<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateGroupsExtendedsTable extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_groups_extendeds', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_groups_extendeds');
    }
}
