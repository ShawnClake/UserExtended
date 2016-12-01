<?php

namespace Clake\Userextended\Updates;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UserGroupsAddRole extends Migration
{

    public function up()
    {
        Schema::table('users_groups', function($table)
        {
            $table->integer('role_id')->default(0);
        });
    }

    public function down()
    {
        Schema::table('users_groups', function($table)
        {
            $table->dropColumn('role_id');
        });
    }

}