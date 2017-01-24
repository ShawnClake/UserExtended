<?php namespace Clake\UserExtended\Classes;

    use Auth;
    use RainLab\User\Models\UserGroup;

    /**
     * TODO: Ensure this class follows SRP
     * TODO: Improve error checking
     * TODO: Change function names to be lower case and enforce consistent naming and function styles
     */

    /**
     * Class UserGroupManager
     * @package Clake\UserExtended\Classes
     *
     * Handles all interactions with groups on a user level
     */
class UserGroupManager extends StaticFactory {

    // Stores an array of UserGroups. ["GroupName" => "GroupDescriptionObject"]
    public $userGroups;

    // Stores the user we are getting groups for
    private $user;

    /**
     * Pass a user object to get groups for that user
     * @param null $user
     * @return \Clake\UserExtended\Classes\UserGroupManager|null
     */
    public function using ($user = null)
    {
        if($user == null)
            $user = UserUtil::getLoogedInUserExtendedUser();

        $this->$user = $user;

        return $this;
    }

    /**
     * Sets the class up to use the currently logged in user
     * @return \Clake\UserExtended\Classes\UserGroupManager|null
     */
    public function currentUser() {

        $this->user = UserUtil::getLoogedInUserExtendedUser();

        return $this;
    }

    /**
     * Returns the logged in user, if available, and touches
     * the last seen timestamp.
     * @deprecated
     * @return RainLab\User\Models\User
     */
    private function getLoggedInUser()
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
    public function all($user = null)
    {

        if($user == null)
            $user = $this->user;

        $userid = $user["id"];

        $usergroup = UserGroup::all();

        $groups = [];

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

        $this->userGroups = $groups;

        return $this;

    }

    /**
     * Get the User Groups the user is in. Only returns the variable - doesn't do the logic
     * @return mixed
     */
    public function getUserGroups() {

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

        return array_key_exists(strtolower($group), $groups);

    }

}