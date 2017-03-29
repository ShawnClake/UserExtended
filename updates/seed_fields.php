<?php namespace Clake\UserExtended\Updates;

use Clake\Userextended\Models\Field;
use October\Rain\Database\Updates\Seeder;

class SeedFields extends Seeder
{
    public function run()
    {
        if (Field::whereCode('phone')->count() == 0) {
            UserGroup::create([
                'name' => 'Phone Number',
                'code' => 'phone',
                'description' => 'Phone number',
                'type' => 'UE_FORM_TEXT',

            ]);
        }

    }
}