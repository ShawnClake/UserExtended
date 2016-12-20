<?php

namespace Clake\Userextended\Updates;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UserAddSettings extends Migration
{

    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->text('settings')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('settings');
        });
    }

}