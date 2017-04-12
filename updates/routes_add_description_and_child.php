<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class RoutesAddDescriptionAndChild extends Migration
{

    public function up()
    {
        Schema::table('clake_userextended_routes', function($table)
        {
            $table->text('description');
            $table->boolean('cascade')->default(true);
        });
    }

    public function down()
    {
        Schema::table('clake_userextended_routes', function($table)
        {
            $table->dropColumn('description');
            $table->dropColumn('cascade');
        });
    }

}