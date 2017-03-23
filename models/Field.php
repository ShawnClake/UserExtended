<?php namespace Clake\Userextended\Models;

use Clake\UserExtended\Classes\FieldManager;
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

    protected $jsonable = [
        'data',
        'flags',
        'validation'
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

    /*
     *         $field = new Field();
        $field->data = ['test' => 'hi', 'new' => ['hi' => 'bye', 'no' => 'yes']];
        $field->name = 'yoyobob';
        $field->code = 'yoyo-bobbyorr';
        $field->description = 'hiya';
        $field->validation = '';
        $field->flags = ['enabled' => true];
        $field->save();
     *
     */

    /**
     * Handles the automated settings of the sort order for roles.
     */
    public function beforeCreate()
    {
        $this->checkFlags();

        if(!isset($this->data))
            $this->data = [];
        //$this->sort_order = RoleManager::with($this->group->code)->countRoles() + 1;

        // TODO: Does this throw an error with 0 entries in the DB?
        $this->sort_order = Field::all()->count() + 1;
    }

    /**
     * Ensures we aren't breaking the existing sort order by saving a sort order that doesn't make sense.
     * @return bool
     */
    public function beforeUpdate()
    {
        $this->checkFlags();

        $total = Field::all()->count();
        if(!(($this->sort_order <= $total) && ($this->sort_order > 0)))
        {
            return false;
        }

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
        $total = Field::all()->count();
        $myOrder = $this->sort_order;

        if($myOrder === $total)
            return true;

        $fields = FieldManager::all()->getSortedFields();

        $difference = $total - $myOrder;

        for($i = 0; $i < $difference; $i++)
        {
            $field = $fields[$total - $i];
            $field->sort_order = $total - $i - 1;
            $field->save();
        }
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

    public function checkFlags()
    {
        $flags = [];

        $flags['enabled'] = isset($this->flags['enabled']) ? $this->flags['enabled'] : false;
        $flags['registerable'] = isset($this->flags['registerable']) ? $this->flags['registerable'] : true;
        $flags['editable'] = isset($this->flags['editable']) ? $this->flags['editable'] : true;
        $flags['encrypt'] = isset($this->flags['encrypt']) ? $this->flags['encrypt'] : false;

        $this->flags = $flags;
    }

    public function validationToString($english = false)
    {
        if(empty($this->validation['additional']))
            $validation = [];
        else
            $validation = explode('|', $this->validation['validation']);

        if(!empty($this->validation['content']))
            $validation[] = $this->validation['content'];

        if(!empty($this->validation['regex']))
            $validation[] = 'regex:' . $this->validation['regex'];

        if(!empty($this->validation['min']))
            $validation[] = 'min:' . $this->validation['min'];

        if(!empty($this->validation['max']))
            $validation[] = 'max:' . $this->validation['max'];

        if(isset($this->validation['flags']))
        {
            foreach($this->validation['flags'] as $vFlag)
            {
                $validation[] = $vFlag;
            }
        }

        return implode('|', $validation);
    }

}