<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateRouteRestriction extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_route_restriction', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('route_id');
            $table->integer('role_id');
            $table->integer('group_id');
            $table->integer('user_id');
            $table->integer('ip');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_route_restriction');
    }
}
