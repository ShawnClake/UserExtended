<?php

namespace Clake\UserExtended\Classes;


use Clake\Userextended\Models\GroupsExtended;
use October\Rain\Support\Collection;

/**
 * Class RoleManager
 * @package Clake\UserExtended\Classes
 *
 * Handles all interactions with roles on a group level (Global level)
 */
class RoleManager
{
    // The group instance
    public $group;

    // A list of roles in that group
    public $roles;

    /**
     * Creating the class and filling it with the roles for the group specified.
     * @param $code
     * @return static
     */
    public static function initGroupRolesByCode($code)
    {
        $instance = new static;
        $instance->group = GroupsExtended::where('code', $code)->first();
        if($instance->group != null)
            $instance->roles = $instance->group->roles;

        return $instance;
    }

    /**
     * Creating the class but not filling it
     * @return static
     */
    public function init()
    {
        $instance = new static;
        return $instance;
    }

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
     * @return array
     */
    public function get()
    {
        return $this->roles;
    }

    /**
     * Goes through the roles attached to this instance and runs ->save() on each
     */
    public function save()
    {
        foreach($this->roles as $role)
        {
            $role->save();
        }
    }

    /**
     * Returns a count of roles in the selected group
     * @return mixed
     */
    public function count()
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

        $sorted = $this->getGroupRolesByOrdering();

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
        if($roleSortOrder > $this->count() - 1)
            return;

        $sorted = $this->getGroupRolesByOrdering();

        $movingUp = $sorted[$roleSortOrder + 1];
        $movingDown = $sorted[$roleSortOrder];

        $movingUp->sort_order = $roleSortOrder;
        $movingDown->sort_order = $roleSortOrder + 1;

        $movingUp->save();
        $movingDown->save();
    }

    /**
     * Sorts the Collection of roles by sort_order and then returns it
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
        $sorted = $this->getGroupRolesByOrdering();

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
     * Useful for promoting, demoting, and getting a sense of heirarchy.
     * @param GroupsExtended $group
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

}