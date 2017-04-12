<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateRoutesPivotTable extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_routes_pivot', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('route_id')->unsigned();
            $table->integer('restriction_id')->unsigned();
            $table->primary(['route_id', 'restriction_id']);
        });

        Schema::table('clake_userextended_route_restriction', function($table)
        {
            $table->dropColumn('route_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_routes_pivot');

        Schema::table('clake_userextended_route_restriction', function($table)
        {
            $table->integer('route_id');
        });
    }
}
