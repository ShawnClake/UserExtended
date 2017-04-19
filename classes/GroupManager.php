<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\GroupsExtended;
use Clake\Userextended\Models\Role;
use Clake\Userextended\Models\UsersGroups;
use October\Rain\Support\Collection;
use Illuminate\Support\Facades\Validator;
use RainLab\User\Models\UserGroup;

/**
 * User Extended by Shawn Clake
 * Class GroupManager
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 *
 * Handles all interaction across groups on a global level rather than a user level.
 * @method static GroupManager allGroups GroupManager
 * @package Clake\UserExtended\Classes
 */
class GroupManager extends StaticFactory
{
    /**
     * A collection of groups
     * @var
     */
    private $groups;

    /**
     * Seeds default basic user groups for the application
     */
    public static function seedBasicUserGroups()
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

    /**
     * Creates a new group and returns it after saved
     * @param $name
     * @param $description
     * @param $code
     * @return GroupsExtended
     */
    public static function createGroup($name, $description, $code)
    {
        if(empty($code))
            $code = $name;

        $code = str_slug($code, "-");

        $validator = Validator::make(
            [
                'name' => $name,
                'description' => $description,
                'code' => $code,
            ],
            [
                'name' => 'required|min:3',
                'description' => 'required|min:8',
                'code' => 'required',
            ]
        );

        if($validator->fails())
        {
            return $validator;
        }

        if(GroupsExtended::code($code)->count() > 0)
            return false;

        $group = new GroupsExtended;
        $group->name = $name;
        $group->description = $description;
        $group->code = $code;
        $group->save();
        return $validator;
    }

    /**
     * Deletes a group
     * @param $groupCode
     */
    public static function deleteGroup($groupCode)
    {
        $group = GroupManager::findGroup($groupCode);

        if(!isset($group))
            return;

        $roles = Role::rolesInGroup($groupCode)->get();

        foreach($roles as $role)
        {
            RoleManager::updateRole($role->code, 1, null, null, null, 0, true);
        }

        $associations = UsersGroups::byGroup($groupCode)->get();

        foreach($associations as $row)
        {
            $row->delete();
        }

        $group->delete();

        self::fixGroupSort();
    }

    /**
     * Updates a group
     * @param $groupCode
     * @param null $name
     * @param null $description
     * @param null $code
     * @return bool|Validator
     */
    public static function updateGroup($groupCode, $name = null, $description = null, $code = null)
    {
        $group = GroupManager::findGroup($groupCode);

        if(isset($name)) $group->name = $name;
        if(isset($description)) $group->description = $description;
        if(isset($code)) $group->code = str_slug($code, "-");

        $validator = Validator::make(
            [
                'name' => $group->name,
                'description' => $group->description,
                'code' => $group->code,
            ],
            [
                'name' => 'required|min:3',
                'description' => 'required|min:8',
                'code' => 'required',
            ]
        );

        if($validator->fails())
        {
            return $validator;
        }

        if(GroupsExtended::code($group->code)->count() > 1)
            return false;

        $group->save();

        return $validator;
    }

    /**
     * Returns a group model where the group code is: code=$code
     * @param $code
     * @return mixed
     */
    public static function findGroup($code)
    {
        return GroupsExtended::where('code', $code)->first();
    }

    /**
     * Creates and fills the class with all of the groups that exist in the applciation
     * @return $this
     */
    public function allGroupsFactory()
    {
        $this->groups = new Collection();

        $groups = GroupsExtended::all();

        foreach($groups as $group)
        {
            $this->groups->put($group->code, $group);
        }

        return $this;
    }

    /**
     * Returns a count of how many groups there are
     * @return mixed
     */
    public function countGroups()
    {
        return $this->groups->count();
    }

    /**
     * Returns a count of how many roles there are in a specific group.
     * @param $groupCode
     * @return bool
     */
    public function countGroupRoles($groupCode)
    {
        if(empty($this->groups))
            return false;
        return $this->groups->get($groupCode)->roles()->count();
    }

    /**
     * Returns the collection of groups.
     * @return mixed
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Returns a set of groups sorted by sort order
     * @return array
     */
    public static function getSortedGroups()
    {
        $groups = [];

        foreach(self::allGroups()->getGroups() as $group)
        {
            $groups[$group["sort_order"]] = $group;
        }

        ksort($groups);

        return $groups;
    }

    /**
     * Fixes the group sort order
     */
    public static function fixGroupSort()
    {
        $groups = GroupManager::getSortedGroups();

        $count = 0;
        foreach($groups as $group)
        {
            $count++;
            $group->sort_order = $count;
            $group->save();
        }
    }

}