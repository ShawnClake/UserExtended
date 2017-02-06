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
 * Class Roles
 * @package Clake\Userextended\Models
 * @method static Role rolesInGroup($groupCode) Query
 */
class Role extends Model
{
    //use Sortable;
    use Timezonable;

    use SoftDelete;

    /**
     * Provides an override for ignoring sort_order checks for onCreate, onUpdate, and onDelete
     * @var bool
     */
    public $ignoreChecks = false;

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

    /**
     * Returns a collection of users which have a role
     * @return array|Collection
     */
    public function getUsersInRole()
    {
        $relations = UsersGroups::byRole($this->code)->get();

        $users = new Collection;

        foreach($relations as $relation)
        {
            $users[] = UserExtended::where('id', $relation->user_id)->first();
        }

        return $users;
    }

    /**
     * Gets roles related to a group specified by the passed in parameter of groupCode
     * @param $query
     * @param $groupCode
     * @return mixed
     */
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
        if($this->group_id == -1)
        {
            $this->sort_order = 1;
            $this->group_id = 0;
        }
        else
            $this->sort_order = RoleManager::for($this->group->code)->countRoles() + 1;
    }

    /**
     * Ensures we aren't breaking the existing sort order by saving a sort order that doesn't make sense.
     * @return bool
     */
    public function beforeUpdate()
    {
        if($this->ignoreChecks)
            return true;

        $total = RoleManager::for($this->group->code)->countRoles();

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
        if($this->group_id == 0)
            return true;

        $total = RoleManager::for($this->group->code)->countRoles();
        $myOrder = $this->sort_order;

        if($myOrder === $total)
            return true;

        $roles = RoleManager::for($this->group->code)->getSortedGroupRoles();

        $difference = $total - $myOrder;

        for($i = 0; $i < $difference; $i++)
        {
            $role = $roles[$total - $i];
            $role->sort_order = $total - $i - 1;
            $role->save();
        }

    }

    /**
     * Adds a role to a relational entry in UsersGroups
     * @param $userObj
     * @param $groupId
     * @return bool
     */
    public static function addUser($userObj, $groupId, $roleId = 0)
    {
        if(UsersGroups::where('user_id', $userObj->id)->where('user_group_id', $groupId)->count() <= 0)
            return false;

        $row = UsersGroups::where('user_id', $userObj->id)->where('user_group_id', $groupId)->first();
        $row->role_id = $roleId;
        $row->save();
    }



}