<?php namespace Clake\Userextended\Models;

use Clake\UserExtended\Classes\FieldManager;
use Model;
use October\Rain\Support\Collection;
use Clake\UserExtended\Traits\Timezonable;
use October\Rain\Database\Traits\SoftDelete;
use Cms\Classes\Page;

/**
 * User Extended by Shawn Clake
 * Class Route
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Models
 */
class Route extends Model
{
    use Timezonable;

    use SoftDelete;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'clake_userextended_routes';

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
        'last_accessed_at',
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
    public $hasMany = [
        'restrictions' => ['Clake\Userextended\Models\RouteRestriction', 'key' => 'route_id', 'otherKey' => 'id']
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function getPossibleRoutes()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'url' );
    }

}