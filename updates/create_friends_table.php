<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateFriendsTable extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_friends', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_that_sent_request');
            $table->integer('user_that_accepted_request');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_friends');
    }
}
