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
                'code' => 'core-suspended',
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

        if (Field::whereCode('core-banned')->count() == 0) {
            Field::create([
                'name' => 'Banned',
                'code' => 'core-banned',
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

        if (Field::whereCode('core-temp-banned')->count() == 0) {
            Field::create([
                'name' => 'Temp Banned',
                'code' => 'core-temp-banned',
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

        if (Field::whereCode('core-privacy-can-comment')->count() == 0) {
            Field::create([
                'name' => 'Privacy Setting who can comment',
                'code' => 'core-privacy-can-comment',
                'description' => '[Core Field] Stores a users privacy setting for who can leave them comments.',
                'type' => 'UE_FORM_NUMBER',
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
                    'placeholder' => 'Octal Code..',
                    'class'       => '',
                    'core'        => true,
                ],
                'flags' => [
                    'enabled'      => true,
                    'registerable' => false,
                    'editable'     => false,
                    'encrypt'      => false,
                ],
            ]);
        }

        if (Field::whereCode('core-privacy-view-profile')->count() == 0) {
            Field::create([
                'name' => 'Privacy Setting who can view a profile',
                'code' => 'core-privacy-view-profile',
                'description' => '[Core Field] Stores a users privacy setting for who can view their profile.',
                'type' => 'UE_FORM_NUMBER',
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
                    'placeholder' => 'Octal Code..',
                    'class'       => '',
                    'core'        => true,
                ],
                'flags' => [
                    'enabled'      => true,
                    'registerable' => false,
                    'editable'     => false,
                    'encrypt'      => false,
                ],
            ]);
        }

        if (Field::whereCode('core-privacy-can-search')->count() == 0) {
            Field::create([
                'name' => 'Privacy Setting who can search them',
                'code' => 'core-privacy-can-search',
                'description' => '[Core Field] Stores a users privacy setting for who can find them in search.',
                'type' => 'UE_FORM_NUMBER',
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
                    'placeholder' => 'Octal Code..',
                    'class'       => '',
                    'core'        => true,
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