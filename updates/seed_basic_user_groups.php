<?php namespace Clake\UserExtended\Updates;

use RainLab\User\Models\UserGroup;
use October\Rain\Database\Updates\Seeder;

class SeedUserGroupsTable extends Seeder
{
    public function run()
    {
        UserGroup::create([
            'name' => 'Admin',
            'code' => 'admin',
            'description' => 'Administrator group'
        ]);

        UserGroup::create([
            'name' => 'Friend',
            'code' => 'friend',
            'description' => 'Generalized friend group.'
        ]);

        UserGroup::create([
            'name' => 'Guest',
            'code' => 'guest',
            'description' => 'Generalized guest group.'
        ]);

        UserGroup::create([
            'name' => 'Tester',
            'code' => 'tester',
            'description' => 'Access bleeding edge features'
        ]);

        UserGroup::create([
            'name' => 'Debugger',
            'code' => 'debugger',
            'description' => 'Debug text, buttons, and visuals appear on the pages'
        ]);

        UserGroup::create([
            'name' => 'Developer',
            'code' => 'developer',
            'description' => 'Access to the dev tools and options'
        ]);

        UserGroup::create([
            'name' => 'Banned',
            'code' => 'banned',
            'description' => 'Banned from viewing pages'
        ]);

    }
}
