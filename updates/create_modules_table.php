<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateModulesTable extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_modules', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->default('')->unique();
            $table->string('author')->default('');
            $table->text('description')->default('');
            $table->string('version')->default('');
            $table->boolean('visible')->default(true);
            $table->boolean('enabled')->default(true);
            $table->boolean('locked')->default(false);
            $table->boolean('updated')->default(true);
            $table->json('flags');
            $table->softDeletes();
            $table->timestamps();
            $table->timestamp('module_updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_modules');
    }
}
