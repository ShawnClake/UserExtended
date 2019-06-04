<?php

namespace Tohur\SocialConnect\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateSocialProvidersTable extends Migration {

    public function up() {
        Schema::create('clake_userextended_providers', function($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->string('provider_id')->default('');
            $table->string('provider_token')->default('');
            $table->index(['provider_id', 'provider_token'], 'provider_id_token_index');
        });

        Schema::table('users', function($table) {
            $table->string('clake_userextended_user_providers')->nullable();
        });
    }

    public function down() {
        Schema::drop('clake_userextended_providers');
        Schema::table('users', function($table) {
            $table->dropColumn('clake_userextended_user_providers');
        });
    }

}
