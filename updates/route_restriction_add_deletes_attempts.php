<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class RouteRestrictionAddDeletesAttempts extends Migration
{

    public function up()
    {
        Schema::table('clake_userextended_route_restriction', function($table)
        {
            $table->string('ip')->change();
            $table->integer('attempts');
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('clake_userextended_route_restriction', function($table)
        {
            $table->integer('ip')->change();
            $table->dropColumn('attempts');
            $table->dropColumn('deleted_at');
        });
    }

}