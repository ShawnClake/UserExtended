<?php

namespace Clake\Userextended\Updates;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UsersGroupsRefactor extends Migration
{

    public function up()
    {
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('users_groups');

        Schema::create('user_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('code')->nullable()->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('users_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('user_group_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_groups');
        Schema::dropIfExists('users_groups');

        Schema::create('user_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('code')->nullable()->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('users_groups', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('user_id')->unsigned();
            $table->integer('user_group_id')->unsigned();
            $table->primary(['user_id', 'user_group_id'], 'user_group');
        });
    }

}