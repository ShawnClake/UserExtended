<?php namespace Clake\Userextended\Models;

use Model;
use Clake\UserExtended\Traits\Timezonable;
use October\Rain\Database\Traits\SoftDelete;

/**
 * User Extended by Shawn Clake
 * Class RouteRestriction
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Models
 */
class RouteRestriction extends Model
{
    use Timezonable;

    use SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'clake_userextended_route_restriction';

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

    protected $jsonable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'user'  => ['Clake\Userextended\Models\UserExtended', 'key' => 'user_id', 'otherKey' => 'id'],
        'group' => ['Clake\Userextended\Models\GroupsExtended', 'key' => 'group_id', 'otherKey' => 'id'],
        'role'  => ['Clake\Userextended\Models\Role', 'key' => 'role_id', 'otherKey' => 'id'],
    ];
    public $belongsToMany = [
        'route' => [
            'Clake\Userextended\Models\Route',
            'key' => 'restriction_id',
            'otherKey' => 'route_id',
            'table' => 'clake_userextended_routes_pivot'
        ],
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}