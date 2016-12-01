<?php namespace Clake\Userextended\Models;

use Clake\UserExtended\Classes\GroupManager;
use Clake\UserExtended\Classes\RoleManager;
use Model;
use October\Rain\Support\Collection;

use Clake\UserExtended\Traits\Timezonable;

//use October\Rain\Database\Traits\Sortable;
//use October\Rain\Database\Traits\Encryptable

/**
 * Roles Model
 */
class Roles extends Model
{
    //use Sortable;
    use Timezonable;

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



}