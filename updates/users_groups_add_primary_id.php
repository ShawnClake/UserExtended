<?php

namespace Clake\Userextended\Updates;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UsersGroupsAddPrimaryId extends Migration
{

    public function up()
    {
        Schema::table('users_groups', function($table)
        {
            $table->increments('id');
        });
    }

    public function down()
    {
        Schema::table('users_groups', function($table)
        {
            $table->dropColumn('id');
        });
    }

}