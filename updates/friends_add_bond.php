<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class FriendsAddBonds extends Migration
{

    public function up()
    {
        Schema::table('clake_userextended_friends', function($table)
        {
            $table->bigInteger('bond');
        });
    }

    public function down()
    {
        Schema::table('clake_userextended_friends', function($table)
        {
            $table->dropColumn('bond');
        });
    }

}