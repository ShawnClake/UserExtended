<?php namespace Clake\Userextended\Models;

use Model;
use Clake\UserExtended\Traits\Searchable;
use October\Rain\Database\Traits\SoftDelete;
use Clake\UserExtended\Traits\Timezonable;

/**
 * User Extended by Shawn Clake
 * Class Timezone
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Models
 */
class Timezone extends Model
{
    use Searchable;

    use SoftDelete;

    use Timezonable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'clake_userextended_timezones';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'abbr',
        'name',
        'utc',
        'offset',
    ];

    /**
     * @var array
     */
    protected $searchable = [
        'name',
        'abbr',
        'utc',
    ];

    /**
     * @var array
     */
    protected $timezonable = [
        'updated_at',
        'created_at'
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
    public $hasMany = [
        'users' => [
            'Clake\Userextended\Models\UserExtended',
            'key' => 'timezone_id',
        ],
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * Returns a list of timezones setup to populate dropdown menus
     * @return array
     */
    public static function getTimezonesList()
    {
        $timezones = Timezone::all();
        $list = [];

        foreach($timezones as $timezone)
        {
            $list[$timezone->abbr] = '(' . $timezone->abbr . ') ' . $timezone->name;
        }

        ksort($list);

        return $list;
    }

}