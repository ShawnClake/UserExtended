<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateRoutesTable extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_routes', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('route');
            $table->enum('type', [
                'UE_WHITELIST',
                'UE_BLACKLIST',
            ])->default('UE_WHITELIST');
            $table->integer('attempts');
            $table->timestamp('last_accessed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_routes');
    }
}
