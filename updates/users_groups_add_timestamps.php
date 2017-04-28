<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UsersGroupsAddTimestamps extends Migration
{

    public function up()
    {
        Schema::table('users_groups', function($table)
        {
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('users_groups', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }

}