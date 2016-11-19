<?php

namespace Clake\Userextended\Updates;
use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CommentsAddAuthor extends Migration
{

    public function up()
    {
        Schema::table('clake_userextended_comments', function($table)
        {
            $table->integer('author_id');
        });
    }

    public function down()
    {
        Schema::table('clake_userextended_friends', function($table)
        {
            $table->dropColumn('author_id');
        });
    }

}