<?php

namespace Clake\UserExtended\Classes;


use Clake\Userextended\Models\GroupsExtended;
use October\Rain\Support\Collection;

/**
 * TODO: In conjunction with RoleManager, UserGroupManager, and UserRoleManager ensure we are following SRP
 * TODO: Better error checking in case things dont exist
 */

/**
 * Class GroupManager
 * @package Clake\UserExtended\Classes
 *
 * Handles all interaction accross groups on a global level rather than a user level.
 */
class GroupManager extends StaticFactory
{

    // A collection of groups
    private $groups;

    public static function retrieve($code)
    {
        return GroupsExtended::where('code', $code)->first();
    }

    /**
     * Creates and fills the class with all of the groups that exist in the applciation
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
     * Returns a count of how many groups there are
     * @return mixed
     */
    public function count()
    {
        return $this->groups->count();
    }

    /**
     * Returns a count of how many roles there are in a specific group.
     * @param $groupCode
     * @return mixed
     */
    public function roleCount($groupCode)
    {
        return $this->groups->get($groupCode)->roles()->count();
    }

    /**
     * Returns the collection of groups.
     * @return mixed
     */
    public function get()
    {
        return $this->groups;
    }

}