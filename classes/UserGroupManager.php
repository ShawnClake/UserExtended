<?php namespace Clake\UserExtended\Classes;

use Auth;
use RainLab\User\Models\UserGroup;

/**
 * Class UserGroupManager
 * @package Clake\UserExtended\Classes
 */
class UserGroupManager {

    // Stores an array of UserGroups. ["GroupName" => "GroupDescriptionObject"]
    public static $userGroups;

    // Stores the user we are getting groups for
    private static $user;

    // Stores the static instance
    private static $_instance = null;

    /**
     * Pass a user object to get groups for that user
     * @param null $user
     * @return \Clake\UserExtended\Classes\UserGroupManager|null
     */
    public static function Using ($user = null)
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        if($user == null) {
            $user = self::getLoggedInUser();
        }

        self::$user = $user;

        return self::$_instance;
    }

    /**
     * Sets the class up to use the currently logged in user
     * @return \Clake\UserExtended\Classes\UserGroupManager|null
     */
    public static function CurrentUser() {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        self::$user = self::getLoggedInUser();

        return self::$_instance;
    }

    /**
     * Returns the logged in user, if available, and touches
     * the last seen timestamp.
     * @return RainLab\User\Models\User
     */
    private static function getLoggedInUser()
    {
        if (!$user = Auth::getUser()) {
            return null;
        }

        $user->touchLastSeen();

        return $user;
    }


    /**
     * Finds all the groups the user is in and stores that to the class IE $userGroups
     * @param null $user
     * @return $this
     */
    public function All($user = null)
    {

        if($user == null)
            $user = self::$user;

        $userid = $user["id"];

        $usergroup = UserGroup::all();

        $groups = [];

        //$tester = [];

        foreach($usergroup as $key => $value)
        {

            $groupMembers = $value->users()->get();

            $groupcode = $value["code"];

            foreach($groupMembers as $groupkey => $groupval) {
                //array_push($tester, $groupval);
                if($userid === $groupval["id"]) {
                    $groups[strtolower($groupcode)]	= $value;
                }

            }

        }

        //$user = UserUtil::getLoggedInUser();

        //$groups = $user->groups()->get();

        self::$userGroups = $groups;

        return $this;

    }

    /**
     * Get the instance of this class
     * @return $this
     */
    public function Instance() {

        return $this;

    }

    /**
     * Get the User Groups the user is in. Only returns the variable - doesn't do the logic
     * @return mixed
     */
    public function Get() {

        return self::$userGroups;

    }

    /**
     * Returns whether or not the user is a part of a group
     * @param $group
     * @param $groups
     * @return bool
     */
    public function IsInGroup($group, $groups = null)
    {

        if($groups == null)
            $groups = self::$userGroups;

        return array_key_exists(strtolower($group), $groups);

    }




}