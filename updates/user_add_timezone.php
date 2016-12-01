<?php

namespace Clake\Userextended\Updates;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UserAddTimezone extends Migration
{

    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->integer('timezone_id')->default(1);
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('timezone_id');
        });
    }

}