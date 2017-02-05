<?php namespace Clake\Userextended\Models;

use Model;
use October\Rain\Support\Collection;
use RainLab\User\Models\UserGroup;
use October\Rain\Database\Traits\Sortable;

use Clake\UserExtended\Traits\Timezonable;

/**
 * TODO: Add scope functions to easily find group queries
 */

/**
 * Class GroupsExtended
 * @package Clake\Userextended\Models
 */
class GroupsExtended extends UserGroup
{

    use Sortable;

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

    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }

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

}