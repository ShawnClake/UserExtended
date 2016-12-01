<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateUsersGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_users_groups', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_users_groups');
    }
}
