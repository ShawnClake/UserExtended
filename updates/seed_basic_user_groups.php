<?php namespace Clake\UserExtended\Updates;

use Clake\UserExtended\Classes\GroupManager;
use October\Rain\Database\Updates\Seeder;

class SeedUserGroupsTable extends Seeder
{
    public function run()
    {
        GroupManager::seedBasicUserGroups();
    }
}