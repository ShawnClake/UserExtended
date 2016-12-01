<?php

namespace Clake\Userextended\Updates;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UserGroupsRemovePrimaryKeys extends Migration
{

    public function up()
    {
        Schema::table('users_groups', function($table)
        {
            $table->dropPrimary('user_group_id');
            //$table->dropPrimary('user_id');
        });
    }

    public function down()
    {
        Schema::table('users_groups', function($table)
        {
            $table->primary(['user_id', 'user_group_id'], 'user_group');
        });
    }

}