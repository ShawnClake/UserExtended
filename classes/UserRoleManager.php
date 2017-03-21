<?php namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Role;
use Clake\Userextended\Models\UsersGroups;

/**
 * User Extended by Shawn Clake
 * Class UserRoleManager
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 *
 * Handles all interactions with roles on a user level
 *
 * @method static UserRoleManager with($user) UserRoleManager
 * @method static UserRoleManager currentUser UserRoleManager
 * @package Clake\UserExtended\Classes
 */
class UserRoleManager extends StaticFactory
{

    /**
     * A collection of User Roles
     * Format like "groupCode" => RoleModel
     * @var
     */
    private $userRoles;

    /**
     * The user instance
     * @var
     */
    private $user;

    /**
     * Used to setup the class using a User model
     * @param null $user
     * @deprecated Renamed below and supports factory
     * @return static
     */
    /*public function using($user = null)
    {
        if($user == null)
            $user = UserUtil::getLoggedInUser();

        $this->user = $user;

        return $this;
    }*/

    /**
     * Sets up the class using a User model
     * @param null $user
     * @return $this
     */
    public function withFactory($user = null)
    {
        if($user == null)
            $user = UserUtil::getLoggedInUser();

        $this->user = $user;

        return $this;
    }

    /**
     * Used to setup the class using the logged in user
     * @deprecated Renamed below and supports factory
     * @return static
     */
    /*public function currentUserOLD()
    {
        $this->user = UserUtil::getLoggedInUser();

        return $this;
    }*/

    /**
     * Used to setup the class using the logged in user
     * @return $this
     */
    public function currentUserFactory()
    {
        $this->user = UserUtil::getLoggedInUser();

        return $this;
    }

    /**
     * Returns the collection of user roles
     * @deprecated Renamed
     * @return mixed
     */
    /*public function get()
    {
        return $this->userRoles;
    }*/

    /**
     * Returns the collection of user roles
     * @return mixed
     */
    public function getUsersRoles()
    {
        return $this->userRoles;
    }

    /**
     * Preforms the logic for getting which roles the user is a part of
     * @deprecated Renamed below to better suit its purpose
     * @return $this
     */
    /*public function all()
    {
        $roles = UserUtil::castToUserExtendedUser($this->user)->roles;
        $userRoles = [];

        foreach($roles as $role)
        {
            $userRoles[strtolower($role->group->code)] = $role;
        }

        $this->userRoles = $userRoles;

        return $this;
    }*/

    /**
     * Preforms the logic for getting which roles the user is a part of
     * TODO: Just utilize the 'roles' relation on the UserExtended user model if possible
     * @return $this
     */
    public function allRoles()
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

        if($roles == null)
            return false;

        foreach($roles as $role)
        {
            if($roleCode == $role->code)
                return true;
        }

        return false;
    }

    /**
     * Saves changes made to a users roles. Useful ONLY for changing existing groups and roles and
     * not useful for creating or deleting them.
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
        if(!UserGroupManager::currentUser()->allGroups()->isInGroup($groupCode))
            return $this;

        $role = $this->getRoleByGroup($groupCode);

        if($role->sort_order < 2)
            return $this;

        $roles = RoleManager::with($groupCode)->getSortedGroupRoles();

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
        if(!UserGroupManager::currentUser()->allGroups()->isInGroup($groupCode))
            return $this;

        $role = $this->getRoleByGroup($groupCode);

        if($role->sort_order > (GroupManager::allGroups()->countGroupRoles($groupCode) - 1))
            return $this;

        $roles = RoleManager::with($groupCode)->getSortedGroupRoles();

        $newRole = $roles[$role->sort_order + 1];

        $this->setRoleByGroup($groupCode, $newRole);

        $this->saveRoles();

        return $this;
    }

    /**
     * Gives a user a role.
     * If they don't exist in the group required for the role yet, it will also add them to the group
     * @param $roleCode
     * @return bool
     */
    public function addRole($roleCode)
    {
        if($this->isInRole($roleCode))
            return false;

        $group = RoleManager::findRole($roleCode)->group;

        UserGroupManager::with($this->user)->addGroup($group->code);

        $roleId = RoleManager::findRole($roleCode)->id;

        return Role::addUser($this->user, $group->id, $roleId);
    }

    public function removeRole($roleCode)
    {
        if(!$this->isInRole($roleCode))
            return false;

        $group = RoleManager::findRole($roleCode)->group;

        return Role::removeUser($this->user, $group->id);
    }

}