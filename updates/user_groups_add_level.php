<?php

namespace Clake\Userextended\Updates;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UserGroupsAddLevel extends Migration
{

    public function up()
    {
        Schema::table('user_groups', function($table)
        {
            $table->integer('level')->default(0);
        });
    }

    public function down()
    {
        Schema::table('user_groups', function($table)
        {
            $table->dropColumn('level');
        });
    }

}