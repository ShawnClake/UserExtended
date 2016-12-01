<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateTimezonesTable extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_timezones', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('abbr');
            $table->string('name');
            $table->string('utc');
            $table->string('offset');
            $table->integer('count')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_timezones');
    }
}
