<?php namespace Clake\Userextended\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('clake_userextended_fields', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->default('');
            $table->string('code')->unique();
            $table->text('description')->default('');
            $table->enum('type', [
                'UE_FORM_TEXT',
                'UE_FORM_CHECKBOX',
                'UE_FORM_COLOR',
                'UE_FORM_DATE',
                'UE_FORM_EMAIL',
                'UE_FORM_FILE',
                'UE_FORM_NUMBER',
                'UE_FORM_PASSWORD',
                'UE_FORM_RADIO',
                'UE_FORM_RANGE',
                'UE_FORM_TEL',
                'UE_FORM_TIME',
                'UE_FORM_URL',
                'UE_FORM_SWITCH'
            ])->default('UE_FORM_TEXT');
            $table->json('validation');
            $table->json('data');/*->default(json_encode([]));*/
            $table->json('flags');/*->default(json_encode([
                'enabled' => false,
                'registerable' => true,
                'editable' => true,
                'encrypt' => false
            ]));*/
            $table->integer('sort_order')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('clake_userextended_fields');
    }
}
