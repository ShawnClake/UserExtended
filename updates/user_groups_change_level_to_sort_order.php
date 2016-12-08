<?php

namespace Clake\Userextended\Updates;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UserGroupsChangeLevelToSortOrder extends Migration
{

    public function up()
    {
        Schema::table('user_groups', function($table)
        {
            $table->renameColumn('level', 'sort_order');
        });
    }

    public function down()
    {
        Schema::table('user_groups', function($table)
        {
            $table->renameColumn('sort_order', 'level');
        });
    }

}