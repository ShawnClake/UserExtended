<?php namespace Clake\Userextended\Models;

use Clake\UserExtended\Classes\GroupManager;
use Model;
use October\Rain\Support\Collection;
use RainLab\User\Models\UserGroup;
use October\Rain\Database\Traits\Sortable;

use Clake\UserExtended\Traits\Timezonable;

/**
 * Class GroupsExtended
 * @package Clake\Userextended\Models
 * @method static GroupsExtended code($code) Query
 *
 */
class GroupsExtended extends UserGroup
{

    //use Sortable;

    use Timezonable;

    protected $timezonable = [
        'created_at',
        'updated_at'
    ];

    /**
     * Used to manually add relations for the user table
     * UserExtended constructor.
     */
    public function __construct()
    {

        $hasMany = $this->hasMany;
        $hasMany['roles'] = ['Clake\UserExtended\Models\Roles', 'key' => 'group_id'];
        $this->hasMany = $hasMany;

        $belongsToMany = $this->belongsToMany;
        $belongsToMany['users'] = ['RainLab\User\Models\User', 'table' => 'users_groups', 'key' => 'user_group_id'];
        $belongsToMany['users_count'] = ['RainLab\User\Models\User', 'table' => 'users_groups', 'key' => 'user_group_id', 'count' => true];
        $this->belongsToMany = $belongsToMany;

        //'users'       => ['RainLab\User\Models\User', 'table' => 'users_groups'],
        //'users_count' => ['RainLab\User\Models\User', 'table' => 'users_groups', 'count' => true]

        parent::__construct();
    }

    /**
     * Returns the group with the passed in parameter code
     * @param $query
     * @param $code
     * @return mixed
     */
    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Returns a collection of users in a group
     * @return array|Collection
     */
    public function getUsersInGroup()
    {
        $relations = UsersGroups::byGroup($this->code)->get();

        $users = new Collection();

        foreach($relations as $relation)
        {
            $users[] = UserExtended::where('id', $relation->user_id)->first();
        }

        return $users;
    }

    /**
     * Override for ensuring the sort_order makes sense when creating a group
     */
    public function beforeCreate()
    {
        //echo GroupManager::allGroups()->countGroups() + 1;
        $this->sort_order = GroupManager::allGroups()->countGroups() + 1;
    }

}