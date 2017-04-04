<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateIntegratedUsers extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_integrated_users', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id');
            $table->bigInteger('integration_id');
            $table->enum('type', [
                'UE_INTEGRATIONS_FACEBOOK',
                'UE_INTEGRATIONS_DISQUS',
            ])->default('UE_INTEGRATIONS_FACEBOOK');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_integrated_users');
    }
}
