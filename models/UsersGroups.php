<?php namespace Clake\Userextended\Models;

use Model;
use October\Rain\Database\Traits\SoftDelete;

/**
 * User Extended by Shawn Clake
 * Class UsersGroups
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Models
 *
 * @method static UsersGroups byRole($roleCode) Query
 * @method static UsersGroups byGroup($groupCode) Query
 * @method static UsersGroups byUser($userId) Query
 * @method static UsersGroups byUserWithoutRole($groupCode) Query
 */
class UsersGroups extends Model
{

    use SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'users_groups';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'role' => ['Clake\UserExtended\Models\Role', 'key' => 'role_id'],
        'user' => ['Clake\UserExtended\Models\UserExtended', 'key' => 'user_id'],
        'group' => ['Clake\UserExtended\Models\GroupsExtended', 'key' => 'user_group_id'],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * Returns a role based upon the passed in roleCode
     * @param $query
     * @param $roleCode
     * @return mixed
     */
    public function scopeByRole($query, $roleCode)
    {
        $role = Role::where('code', $roleCode)->first();
        if(!isset($role))
            return $query;
        return $query->where('role_id', $role->id);
    }

    /**
     * Returns a group based upon the passed in groupCode
     * @param $query
     * @param $groupCode
     * @return mixed
     */
    public function scopeByGroup($query, $groupCode)
    {
        $group = GroupsExtended::where('code', $groupCode)->first();
        return $query->where('user_group_id', $group->id);
    }

    /**
     * Returns a user based upon the passed in userId
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a list of rows of users in a group but without an assigned role.
     * @param $query
     * @param $groupCode
     * @return mixed
     */
    public function scopeByUsersWithoutRole($query, $groupCode)
    {
        $group = GroupsExtended::where('code', $groupCode)->first();
        return $query->where('user_group_id', $group->id)->where('role_id', 0);
    }

    /**
     * Returns a list of roles with users attached to them
     * TODO
     */
    public static function getAssignedRoles()
    {

    }

    /**
     * Returns a list of groups with users attached to them
     * TODO
     */
    public static function getAssignedGroups()
    {

    }

    /**
     * Adds a relation row to users groups.
     * @param $userObj
     * @param $groupId
     * @param int $roleId
     * @return bool
     */
    public static function addUser($userObj, $groupId, $roleId = 0)
    {
        if(UsersGroups::where('user_id', $userObj->id)->where('user_group_id', $groupId)->count() > 0)
            return false;

        $row = new UsersGroups();
        $row->user_id = $userObj->id;
        $row->user_group_id = $groupId;
        $row->role_id = $roleId;
        $row->save();

        return true;
    }

    /**
     * Remove a user from a group
     * @param $userObj
     * @param $groupId
     * @return bool
     */
    public static function removeUser($userObj, $groupId)
    {
        if(UsersGroups::where('user_id', $userObj->id)->where('user_group_id', $groupId)->count() == 0)
            return false;

        $relation = UsersGroups::where('user_id', $userObj->id)->where('user_group_id', $groupId)->first();
        $relation->delete();
    }



}