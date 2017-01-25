<?php

namespace Clake\UserExtended\Classes;

use Clake\UserExtended\Models\GroupsExtended;
use Clake\Userextended\Models\Roles;
use Clake\Userextended\Models\UsersGroups;
use Clake\UserExtended\Plugin;

/**
 * TODO: Enforce SRP
 * TODO: Ensure the class has the same function useability as the others
 * TODO: Enforce conventions
 */

/**
 * Class UserRoleManager
 * @package Clake\UserExtended\Classes
 *
 * Handles all interactions with roles on a user level
 *
 *
 */
class UserRoleManager extends StaticFactory
{

    // A collection of User Roles
    private $userRoles; // Format like "groupCode" => RoleModel

    // The user instance
    private $user;

    /**
     * Used to setup the class using a User model
     * @param null $user
     * @return static
     */
    public function using($user = null)
    {
        if($user == null)
            $user = UserUtil::getLoggedInUser();

        $this->user = $user;

        return $this;
    }

    /**
     * Used to setup the class using the logged in user
     * @return static
     */
    public function currentUser()
    {
        $this->user = UserUtil::getLoggedInUser();

        return $this;
    }

    /**
     * Returns the collection of user roles
     * @return mixed
     */
    public function get()
    {
        return $this->userRoles;
    }

    /**
     * Preforms the logic for getting which roles the user is a part of
     * @return $this
     */
    public function all()
    {
        $roles = UserUtil::castToUserExtendedUser($this->user)->roles;
        $userRoles = [];

        foreach($roles as $role)
        {
            $userRoles[strtolower($role->group->code)] = $role;
        }

        $this->userRoles = $userRoles;

        return $this;
    }

    /**
     * Returns whether or not the user is in a specific role
     * @param $roleCode
     * @param null $roles
     * @return bool
     */
    public function isInRole($roleCode, $roles = null)
    {
        if($roles == null)
            $roles = $this->userRoles;

        foreach($roles as $role)
        {
            if($roleCode == $role->code)
                return true;
        }

        return false;
    }

    /**
     * Saves changes made to a users roles. Useful ONLY for changing existing groups and roles and
     * not useful for creaitng or deleting them.
     * @return $this
     */
    public function saveRoles()
    {
        $userid = $this->user->id;

        foreach($this->userRoles as $role)
        {
            $pivot = UsersGroups::where('user_id', $userid)->where('user_group_id', $role->group->id)->first();
            $pivot->role_id = $role->id;
            $pivot->save();
        }

        return $this;
    }

    /**
     * Returns the users role for a specific group they are a part of.
     * @param $groupCode
     * @return mixed
     */
    public function getRoleByGroup($groupCode)
    {
        return $this->userRoles[strtolower($groupCode)];
    }

    /**
     * Sets the users role for a specific group they are a part of.
     * DOES NOT SAVE.
     * @param $groupCode
     * @param $role
     */
    public function setRoleByGroup($groupCode, $role)
    {
        $this->userRoles[strtolower($groupCode)] = $role;
    }

    /**
     * All in one function promotion of a user. Use case: Promote a user in a writer group from jr. writer to senior writer
     * This does error checking, and saving.. Use eg: UserRoleManager::currentUser()->all()->promote('writer');
     * @param $groupCode
     * @return $this
     */
    public function promote($groupCode)
    {
        if(!UserGroupManager::currentUser()->all()->isInGroup($groupCode))
            return $this;

        $role = $this->getRoleByGroup($groupCode);

        if($role->sort_order < 2)
            return $this;

        $roleGroup = $role->group;

        $roles = $this->getGroupRolesByOrdering($roleGroup);

        $newRole = $roles[$role->sort_order - 1];

        $this->setRoleByGroup($groupCode, $newRole);

        $this->saveRoles();

        return $this;
    }

    /**
     * All in one function demotion of a user. Please read the promote documentation to learn how to use this function
     * @param $groupCode
     * @return $this
     */
    public function demote($groupCode)
    {
        if(!UserGroupManager::currentUser()->all()->isInGroup($groupCode))
            return $this;

        $role = $this->getRoleByGroup($groupCode);

        if($role->sort_order > (GroupManager::all()->roleCount($groupCode) - 1))
            return $this;

        $roleGroup = $role->group;

        $roles = RoleManager::initGroupRolesByCode($roleGroup)->getGroupRolesByOrdering();

        $newRole = $roles[$role->sort_order + 1];

        $this->setRoleByGroup($groupCode, $newRole);

        $this->saveRoles();

        return $this;
    }

}