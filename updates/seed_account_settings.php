<?php namespace Clake\UserExtended\Updates;

use Carbon\Carbon;
use Clake\Userextended\Models\Field;
use October\Rain\Database\Updates\Seeder;

class SeedAccountSettings extends Seeder
{
    public function run()
    {
        if (Field::whereCode('suspended')->count() == 0) {
            Field::create([
                'name' => 'Suspended',
                'code' => 'suspended',
                'description' => '[Core Field] Stores whether or not a user is suspended.',
                'type' => 'UE_FORM_CHECKBOX',
                'validation'     => [
                    'additional' => '',
                    'content'    => '',
                    'regex'      => '',
                    'min'        => '',
                    'max'        => '',
                    'flags'      => [
                        '',
                    ]
                ],
                'data' => [
                    'placeholder' => 'Suspended..',
                    'class'       => '',
                    'core'        => true
                ],
                'flags' => [
                    'enabled'      => true,
                    'registerable' => false,
                    'editable'     => false,
                    'encrypt'      => false,
                ],
            ]);
        }

        if (Field::whereCode('banned')->count() == 0) {
            Field::create([
                'name' => 'Banned',
                'code' => 'banned',
                'description' => '[Core Field] Stores whether or not a user is banned.',
                'type' => 'UE_FORM_CHECKBOX',
                'validation'     => [
                    'additional' => '',
                    'content'    => '',
                    'regex'      => '',
                    'min'        => '',
                    'max'        => '',
                    'flags'      => [
                        '',
                    ]
                ],
                'data' => [
                    'placeholder' => 'Banned..',
                    'class'       => '',
                    'core'        => true
                ],
                'flags' => [
                    'enabled'      => true,
                    'registerable' => false,
                    'editable'     => false,
                    'encrypt'      => false,
                ],
            ]);
        }

        if (Field::whereCode('temp-banned')->count() == 0) {
            Field::create([
                'name' => 'Temp Banned',
                'code' => 'temp-banned',
                'description' => '[Core Field] Stores whether or not a user is temp banned.',
                'type' => 'UE_FORM_CHECKBOX',
                'validation'     => [
                    'additional' => '',
                    'content'    => '',
                    'regex'      => '',
                    'min'        => '',
                    'max'        => '',
                    'flags'      => [
                        '',
                    ]
                ],
                'data' => [
                    'placeholder' => 'Temp Banned..',
                    'class'       => '',
                    'core'        => true,
                    'unban_date'  => Carbon::now()
                ],
                'flags' => [
                    'enabled'      => true,
                    'registerable' => false,
                    'editable'     => false,
                    'encrypt'      => false,
                ],
            ]);
        }

    }
}