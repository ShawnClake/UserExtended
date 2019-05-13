<?php

namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class UserAddLocation extends Migration {

    public function up() {
        Schema::table('users', function($table) {
            $table->integer('country_id')->unsigned()->nullable()->index();
            $table->integer('state_id')->unsigned()->nullable()->index();
        });
    }

    public function down() {
        Schema::table('users', function($table) {
            $table->dropColumn('country_id');
            $table->dropColumn('state_id');
        });
    }

}
