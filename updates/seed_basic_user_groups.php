<?php namespace Clake\UserExtended\Updates;

use RainLab\User\Models\UserGroup;
use October\Rain\Database\Updates\Seeder;

class SeedUserGroupsTable extends Seeder
{
    public function run()
    {

        if (UserGroup::whereCode('admin')->count() == 0) {
                UserGroup::create([
                    'name' => 'Admin',
                    'code' => 'admin',
                    'description' => 'Administrator group'
                ]);
         }

        if (UserGroup::whereCode('friend')->count() == 0) {
                UserGroup::create([
                    'name' => 'Friend',
                    'code' => 'friend',
                    'description' => 'Generalized friend group.'
                ]);
         }

        if (UserGroup::whereCode('guest')->count() == 0) {
                UserGroup::create([
                    'name' => 'Guest',
                    'code' => 'guest',
                    'description' => 'Generalized guest group'
                ]);
         }

        if (UserGroup::whereCode('tester')->count() == 0) {
                UserGroup::create([
                    'name' => 'Tester',
                    'code' => 'tester',
                    'description' => 'Access bleeding edge features'
                ]);
         }

        if (UserGroup::whereCode('debugger')->count() == 0) {
                UserGroup::create([
                    'name' => 'Debugger',
                    'code' => 'debugger',
                    'description' => 'Debug text, buttons, and visuals appear on the pages'
                ]);
         }

        if (UserGroup::whereCode('developer')->count() == 0) {
                UserGroup::create([
                    'name' => 'Developer',
                    'code' => 'developer',
                    'description' => 'Access to the dev tools and options'
                ]);
         }

        if (UserGroup::whereCode('banned')->count() == 0) {
                UserGroup::create([
                    'name' => 'Banned',
                    'code' => 'banned',
                    'description' => 'Banned from viewing pages'
                ]);
         }

    }
}
