<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\GroupsExtended;
use Clake\Userextended\Models\Role;
use Clake\Userextended\Models\UsersGroups;
use October\Rain\Support\Collection;

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
                'code' => 'required|unique:user_groups,code',
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
        return $group;
    }

    /**
     * Deletes a group
     * @param $groupCode
     */
    public static function deleteGroup($groupCode)
    {
        $group = GroupManager::findGroup($groupCode);
        //echo json_encode($groupCode);
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
     */
    public static function updateGroup($groupCode, $name = null, $description = null, $code = null)
    {
        $group = GroupManager::findGroup($groupCode);

        if(isset($name)) $group->name = $name;
        if(isset($description)) $group->description = $description;
        if(isset($code)) $group->code = $code;

        $validator = Validator::make(
            [
                'name' => $group->name,
                'description' => $group->description,
                'code' => $group->code,
            ],
            [
                'name' => 'required|min:3',
                'description' => 'required|min:8',
                'code' => 'required|unique:user_groups,code',
            ]
        );

        if($validator->fails())
        {
            return $validator;
        }

        $group->save();
    }

    /**
     * @param $code
     * @deprecated Renamed to a better name below.
     * @return mixed
     */
    public static function retrieve($code)
    {
        return GroupsExtended::where('code', $code)->first();
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
     * @deprecated Renamed below and adds factory support
     * @return static
     */
    public function all()
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
     * @deprecated Renamed to follow RoleManager format
     * @return mixed
     */
    public function count()
    {
        return $this->groups->count();
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
     * @deprecated Renamed below and added better error checking
     * @return mixed
     */
    public function roleCount($groupCode)
    {
        return $this->groups->get($groupCode)->roles()->count();
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
     * @deprecated Renamed below to a better name
     * @return mixed
     */
    public function get()
    {
        return $this->groups;
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