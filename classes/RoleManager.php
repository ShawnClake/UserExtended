<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\GroupsExtended;
use Clake\Userextended\Models\Role;
use October\Rain\Support\Collection;

/**
 * User Extended by Shawn Clake
 * Class RoleManager
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 *
 * Handles all interactions with roles on a group level (Global level)
 * @method static RoleManager for($groupCode) RoleManager
 * @package Clake\UserExtended\Classes
 */
class RoleManager extends StaticFactory
{
    /**
     * The group instance
     * @var
     */
    private $group;

    /**
     * A list of roles in that group
     * @var
     */
    private $roles;

    /**
     * Returns a list of roles not currently related to a group.
     * @return mixed
     */
    public static function getUnassignedRoles()
    {
        return Role::where('group_id', 0)->get();
    }

    /**
     * Creates a role and returns it after saving
     * @param $name
     * @param $description
     * @param $code
     * @param int $groupId
     * @return Roles
     */
    public static function createRole($name, $description, $code, $groupId = 0)
    {
        $role = new Role();
        $role->name = $name;
        $role->description = $description;
        $role->code = $code;
        $role->group_id = $groupId;
        $role->save();
        return $role;
    }

    /**
     * Deletes a role
     * TODO: Reset the role_id in UsersGroups associations back to 0 where this role was used.
     * @param $roleCode
     */
    public static function deleteRole($roleCode)
    {
        $role = RoleManager::findRole($roleCode);

        if(!isset($role))
            return;

        $role->delete();
    }

    /**
     * Updates a role
     * @param $roleCode
     * @param null $sortOrder
     * @param null $name
     * @param null $description
     * @param null $code
     * @param null $groupId
     */
    public static function updateRole($roleCode, $sortOrder = null, $name = null, $description = null, $code = null, $groupId = null, $ignoreChecks = false)
    {
        $role = RoleManager::findRole($roleCode);

        if(isset($sortOrder)) $role->sort_order = $sortOrder;
        if(isset($name)) $role->name = $name;
        if(isset($description)) $role->description = $description;
        if(isset($code)) $role->code = $code;
        if(isset($groupId)) $role->group_id = $groupId;

        $role->ignoreChecks = $ignoreChecks;
        $role->save();
        $role->ignoreChecks = false;
    }

    /**
     * Finds and returns a role via RoleCode
     * @param $roleCode
     * @return mixed
     */
    public static function findRole($roleCode)
    {
        return Role::where('code', $roleCode)->first();
    }

    /**
     * Creating the class and filling it with the roles for the group specified.
     * @param $code
     * @deprecated Renamed and supports factory
     * @return static
     */
    public function groupRolesByCode($code)
    {
        $this->group = GroupsExtended::where('code', $code)->first();
        if($this->group != null)
            $this->roles = $this->group->roles;

        return $this;
    }

    /**
     * Fills the class with a group model and role models for the group code passed in.
     * @param $groupCode
     * @return $this
     */
    public function forFactory($groupCode)
    {
        $this->group = GroupsExtended::where('code', $groupCode)->first();
        if($this->group != null)
            $this->roles = $this->group->roles;

        return $this;
    }

    /**
     * @deprecated A renamed version exists below
     * @param $code
     * @return bool
     */
    public function getRoleIfExists($code)
    {
        foreach($this->roles as $role)
        {
            if ($role->code == $code)
                return $role;
        }
        return false;
    }

    /**
     * Returns a role model by passing a role code in.
     * This will only return a role if the role exists in the group.
     * @param $roleCode
     * @return bool
     */
    public function getRole($roleCode)
    {
        foreach($this->roles as $role)
        {
            if ($role->code == $roleCode)
                return $role;
        }
        return false;
    }

    /**
     * Filling the class with roles for the group code passed to this function
     * @param $code
     * @return $this
     */
    public function setGroup($code)
    {
        $this->group = GroupsExtended::where('code', $code)->first();
        if($this->group != null)
            $this->roles = $this->group->roles;
        return $this;
    }

    /**
     * Returns all the roles inside of a group
     * @deprecated Renamed
     * @return array
     */
    public function get()
    {
        return $this->roles;
    }

    /**
     * Returns all the roles inside of a group
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Goes through the roles attached to this instance and runs ->save() on each
     * @deprecated Renamed
     */
    public function save()
    {
        foreach($this->roles as $role)
        {
            $role->save();
        }
    }

    /**
     * Goes through the roles attached to this instance and runs ->save() on each
     */
    public function saveRoles()
    {
        foreach($this->roles as $role)

            $role->save();
    }

    /**
     * Returns a count of roles in the selected group
     * @deprecated Renamed
     * @return mixed
     */
    public function count()
    {
        return $this->roles->count();
    }

    /**
     * Returns a count of roles in the selected group
     * @return mixed
     */
    public function countRoles()
    {
        return $this->roles->count();
    }

    /**
     * Moves a role higher up in the heirarchy for that group
     * @param $roleSortOrder
     */
    public function sortUp($roleSortOrder)
    {
        if($roleSortOrder < 2)
            return;

        $sorted = $this->getSortedGroupRoles();

        $movingUp = $sorted[$roleSortOrder];
        $movingDown = $sorted[$roleSortOrder - 1];

        $movingUp->sort_order = $roleSortOrder - 1;
        $movingDown->sort_order = $roleSortOrder;

        $movingUp->save();
        $movingDown->save();
    }

    /**
     * Moves a role lower down in the heirarchy for that group
     * @param $roleSortOrder
     */
    public function sortDown($roleSortOrder)
    {
        if($roleSortOrder > $this->countRoles() - 1)
            return;

        $sorted = $this->getSortedGroupRoles();

        $movingUp = $sorted[$roleSortOrder + 1];
        $movingDown = $sorted[$roleSortOrder];

        $movingUp->sort_order = $roleSortOrder;
        $movingDown->sort_order = $roleSortOrder + 1;

        $movingUp->save();
        $movingDown->save();
    }

    /**
     * Sorts the Collection of roles by sort_order and then returns it
     * @deprecated Remove this entirely. Switch usage to be like Class->sort()->getRoles();
     * @return mixed
     */
    public function getSorted()
    {
        $this->sort();
        return $this->roles;
    }

    /**
     * Sorts the Collection of Roles by sort_order
     * @return $this
     */
    public function sort()
    {
        $sorted = $this->getSortedGroupRoles();

        $roles = new Collection();

        foreach($sorted as $role)
        {
            $roles->push($role);
        }

        $this->roles = $roles;

        return $this;
    }

    /**
     * Gets a list of roles in a group and sorts it by sort_order
     * Useful for promoting, demoting, and getting a sense of hierarchy.
     * @deprecated Renamed
     * @return array
     */
    public function getGroupRolesByOrdering()
    {
        $groupRoles = [];

        foreach($this->roles as $role)
        {
            $groupRoles[$role["sort_order"]] = $role;
        }

        ksort($groupRoles);

        return $groupRoles;
    }

    /**
     * Gets a list of roles in a group and sorts it by sort_order
     * Useful for promoting, demoting, and getting a sense of hierarchy.
     * @return array
     */
    public function getSortedGroupRoles()
    {
        $groupRoles = [];

        foreach($this->roles as $role)
        {
            $groupRoles[$role["sort_order"]] = $role;
        }

        ksort($groupRoles);

        return $groupRoles;
    }

    /**
     * Fixes the role sort order
     */
    public function fixRoleSort()
    {
        $roles = RoleManager::for($this->group->code)->getSortedGroupRoles();

        $count = 0;
        foreach($roles as $role)
        {
            $count++;
            $role->sort_order = $count;
            $role->save();
        }

    }

}