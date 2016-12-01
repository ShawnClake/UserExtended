<?php
/**
 * Created by PhpStorm.
 * User: Shawn
 * Date: 11/26/2016
 * Time: 1:04 PM
 */

namespace Clake\UserExtended\Classes;


use Clake\Userextended\Models\GroupsExtended;
use October\Rain\Support\Collection;

/**
 * Class GroupManager
 * @package Clake\UserExtended\Classes
 *
 * Handles all interaction accross groups on a global level rather than a user level.
 */
class GroupManager
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
    public static function all()
    {
        $instance = new static;

        $instance->groups = new Collection();

        $groups = GroupsExtended::all();

        foreach($groups as $group)
        {
            $instance->groups->put($group->code, $group);
        }

        return $instance;
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