<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class RoutesSwitchTypes extends Migration
{

    public function up()
    {
        Schema::table('clake_userextended_routes', function($table)
        {
            $table->dropColumn('type');
            $table->boolean('enabled')->default(true);
        });
    }

    public function down()
    {
        Schema::table('clake_userextended_routes', function($table)
        {
            $table->enum('type', [
                'UE_WHITELIST',
                'UE_BLACKLIST',
            ])->default('UE_WHITELIST');
            $table->dropColumn('enabled');
        });
    }

}