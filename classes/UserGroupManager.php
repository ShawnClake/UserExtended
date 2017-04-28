<?php namespace Clake\UserExtended\Classes;

use Auth;
use Clake\Userextended\Models\UsersGroups;

/**
 * User Extended by Shawn Clake
 * Class UserGroupManager
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 *
 * Handles all interactions with groups on a user level
 * @package Clake\UserExtended\Classes
 *
 * @method static UserGroupManager currentUser() UserGroupManager
 * @method static UserGroupManager with($user) UserGroupManager
 */
class UserGroupManager extends StaticFactory {

    /**
     * Stores an array of UserGroups. ["GroupName" => "GroupDescriptionObject"]
     * @var
     */
    private $userGroups;

    /**
     * Stores the user object for the member of the groups we are getting
     * @var
     */
    private $user;

    /**
     * Pass a user object to get groups for that user
     * @param null $user
     * @return $this
     */
    public function withFactory($user = null)
    {
        if($user == null)
            $user = UserUtil::getLoggedInUserExtendedUser();

        $this->user = $user;

        $this->allGroups();

        return $this;
    }

    /**
     * Sets the class up to use the currently logged in user
     * @return \Clake\UserExtended\Classes\UserGroupManager|null
     */
    public function currentUserFactory()
    {
        $this->user = UserUtil::getLoggedInUserExtendedUser();
        return $this;
    }

    /**
     * Finds all the groups the user is in and stores that to the class IE $userGroups
     * TODO: Can we not just use the 'groups' relation provided on RainLab.Users User model
     * @return $this
     */
    public function allGroups()
    {
        $user = $this->user;
        if(!isset($user))
            return $this;

        $groups = [];

        foreach($user->groups as $group)
            $groups[strtolower($group->code)]	= $group;

        $this->userGroups = $groups;

        return $this;
    }

    /**
     * Returns a collection of groups a user is in
     * @return mixed
     */
    public function getUsersGroups()
    {
        return $this->userGroups;
    }

    /**
     * Returns whether or not the user is a part of a group
     * @param $group
     * @param $groups
     * @return bool
     */
    public function isInGroup($group, $groups = null)
    {
        if($groups == null)
            $groups = $this->userGroups;

        if($groups == null)
            return false;

        return array_key_exists(strtolower($group), $groups);
    }

    /**
     * Add a group to a user
     * @param $groupCode
     * @return bool
     */
    public function addGroup($groupCode)
    {
        if($this->isInGroup($groupCode))
            return false;

        $group = GroupManager::findGroup($groupCode);

        return UsersGroups::addUser($this->user, $group->id);
    }

    /**
     * Remove a group from a user
     * @param $groupCode
     * @return bool
     */
    public function removeGroup($groupCode)
    {
        if(!$this->isInGroup($groupCode))
            return false;

        $group = GroupManager::findGroup($groupCode);

        return UsersGroups::removeUser($this->user, $group->id);
    }

}