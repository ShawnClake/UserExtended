<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class RouteRestrictionAddFlags extends Migration
{

    public function up()
    {
        Schema::table('clake_userextended_route_restriction', function($table)
        {
            $table->string('name');
            $table->text('description');
            $table->enum('type', [
                'UE_WHITELIST',
                'UE_BLACKLIST',
            ])->default('UE_WHITELIST');

            $table->integer('user_id')->nullable()->change();
            $table->integer('role_id')->nullable()->change();
            $table->integer('group_id')->nullable()->change();
            $table->string('ip')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('clake_userextended_route_restriction', function($table)
        {
            $table->dropColumn('type');
            $table->dropColumn('name');
            $table->dropColumn('description');
        });
    }

}