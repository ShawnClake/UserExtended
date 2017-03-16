<?php namespace Clake\Userextended\Models;

use Model;
use October\Rain\Support\Collection;
use Clake\UserExtended\Traits\Timezonable;
use October\Rain\Database\Traits\SoftDelete;

/**
 * User Extended by Shawn Clake
 * Class Field
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Models
 */
class Field extends Model
{
    use Timezonable;

    use SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'clake_userextended_fields';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array
     */
    protected $timezonable = [
        'updated_at',
        'created_at',
        'deleted_at'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * Handles the automated settings of the sort order for roles.
     */
    public function beforeCreate()
    {
        //$this->sort_order = RoleManager::with($this->group->code)->countRoles() + 1;
    }

    /**
     * Ensures we aren't breaking the existing sort order by saving a sort order that doesn't make sense.
     * @return bool
     */
    public function beforeUpdate()
    {
        /*$total = RoleManager::with($this->group->code)->countRoles();

        if(!(($this->sort_order <= $total) && ($this->sort_order > 0)))
        {
            return false;
        }*/
    }

    /**
     * Handles the bubbling down of all the roles in a group when deleting an intermediate role
     * @return bool
     */
    public function beforeDelete()
    {
        /*$total = RoleManager::with($this->group->code)->countRoles();
        $myOrder = $this->sort_order;

        if($myOrder === $total)
            return true;

        $roles = RoleManager::with($this->group->code)->getSortedGroupRoles();

        $difference = $total - $myOrder;

        for($i = 0; $i < $difference; $i++)
        {
            $role = $roles[$total - $i];
            $role->sort_order = $total - $i - 1;
            $role->save();
        }*/
    }

    /**
     * Returns the role with the passed in parameter code
     * @param $query
     * @param $code
     * @return mixed
     */
    public function scopeCode($query, $code)
    {
        return $query->where('code', $code);
    }

}