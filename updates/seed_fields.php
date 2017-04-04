<?php namespace Clake\UserExtended\Updates;

use Clake\Userextended\Models\Field;
use October\Rain\Database\Updates\Seeder;

class SeedFields extends Seeder
{
    public function run()
    {
        if (Field::whereCode('nickname')->count() == 0) {
            Field::create([
                'name' => 'Nickname',
                'code' => 'nickname',
                'description' => 'A users nickname. These are not unique.',
                'type' => 'UE_FORM_TEXT',
                'validation' => [
                    'additional' => '',
                    'content' => 'alpha_num',
                    'regex' => '',
                    'min' => '3',
                    'max' => '13',
                    'flags' => [
                        'required',
                    ]
                ],
                'data' => [
                    'placeholder' => 'Nickname..',
                    'class' => ''
                ],
                'flags' => [
                    'enabled' => true,
                    'registerable' => false,
                    'editable' => true,
                    'encrypt' => false,
                ],
            ]);
        }

    }
}