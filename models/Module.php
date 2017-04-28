<?php namespace Clake\Userextended\Models;

use Model;
use Clake\UserExtended\Traits\Searchable;
use October\Rain\Database\Traits\SoftDelete;
use Clake\UserExtended\Traits\Timezonable;

/**
 * User Extended by Shawn Clake
 * Class Module
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Models
 */
class Module extends Model
{
    use Searchable;

    use SoftDelete;

    use Timezonable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'clake_userextended_modules';

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
    protected $searchable = [
        'name',
        'description'
    ];

    protected $jsonable = [
        'flags'
    ];

    /**
     * @var array
     */
    protected $timezonable = [
        'updated_at',
        'created_at',
        'module_updated_at',
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

}