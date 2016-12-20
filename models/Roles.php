<?php namespace Clake\Userextended\Models;

use Clake\UserExtended\Classes\GroupManager;
use Clake\UserExtended\Classes\RoleManager;
use Model;
use October\Rain\Support\Collection;

use Clake\UserExtended\Traits\Timezonable;

//use October\Rain\Database\Traits\Sortable;
//use October\Rain\Database\Traits\Encryptable

use October\Rain\Database\Traits\SoftDelete;

/**
 * TODO: Add scope functions to improve querying
 * TODO: Improve error checking for creating and updating roles
 * TODO: Add beforeDelete which ensures the other roles are fixed in case a role is removed
 * TODO: Rename to Role to fit the convention
 */

/**
 * Class Roles
 * @package Clake\Userextended\Models
 */
class Roles extends Model
{
    //use Sortable;
    use Timezonable;

    use SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'clake_userextended_roles';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    protected $timezonable = [
        'updated_at',
        'created_at'
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'group' => [
            'Clake\UserExtended\Models\GroupsExtended',
            'key' => 'group_id',
        ],
    ];

    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function scopeRolesInGroup($query, $groupCode)
    {
        $group = GroupsExtended::where('code', $groupCode)->first();
        return $query->where('group_id', $group->id);
    }

    /**
     * Handles the automated settings of the sort order for roles.
     */
    public function beforeCreate()
    {
        $this->sort_order = RoleManager::initGroupRolesByCode($this->group->code)->count() + 1;
    }

    /**
     * Ensures we aren't breaking the existing sort order by saving a sort order that doesn't make sense.
     * @return bool
     */
    public function beforeUpdate()
    {
        $total = RoleManager::initGroupRolesByCode($this->group->code)->count();

        if(!(($this->sort_order <= $total) && ($this->sort_order > 0)))
        {
            return false;
        }
    }

    /**
     * Handles the bubbling down of all the roles in a group when deleting an intermediate role
     * @return bool
     */
    public function beforeDelete()
    {
        $total = RoleManager::initGroupRolesByCode($this->group->code)->count();
        $myOrder = $this->sort_order;

        if($myOrder === $total)
            return true;

        $roles = RoleManager::initGroupRolesByCode($this->group->code)->getGroupRolesByOrdering();

        $difference = $total - $myOrder;

        for($i = 0; $i < $difference; $i++)
        {
            $role = $roles[$total - $i];
            $role->sort_order = $total - $i - 1;
            $role->save();
        }

    }



}