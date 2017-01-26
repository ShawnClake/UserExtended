<?php namespace Clake\Userextended\Models;

use Model;

/**
 * TODO: Rename to UsersGroup to follow convention
 * TODO: Add scope functions to improve queryability
 */

/**
 * Class UsersGroups
 * @package Clake\Userextended\Models
 */
class UsersGroups extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'users_groups';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    public $timestamps = false;

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'role' => ['Clake\UserExtended\Models\Roles', 'key' => 'role_id'],
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
     * @param $query
     * @param $roleCode
     * @return mixed
     */
    public function scopeByRole($query, $roleCode)
    {
        $role = Roles::where('code', $roleCode)->first();
        if(!isset($role))
            return $query;
        return $query->where('role_id', $role->id);
    }

    /**
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
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     *
     */
    public static function getAssignedRoles()
    {

    }

    /**
     *
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



}