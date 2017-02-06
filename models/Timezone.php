<?php namespace Clake\Userextended\Models;

use Model;
use Clake\UserExtended\Traits\Searchable;
use October\Rain\Database\Traits\SoftDelete;
use Clake\UserExtended\Traits\Timezonable;

/**
 * Class Timezone
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

    protected $searchable = [
        'name',
        'abbr',
        'utc',
    ];

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
    public $hasMany = [
        'users' => [
            'Clake\Userextended\Models\UserExtended',
            'key' => 'timezone_id',
        ],
    ];
    public $belongsTo = [];
    public $belongsToMany = [

    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * Returns a list of timezones setup to populate dropdown menus
     * @return array
     */
    public function getTimezonesList()
    {
        $timezones = Timezone::all();
        $list = [];

        foreach($timezones as $timezone)
        {
            $list[] = [$timezone->abbr => $timezone->name];
        }

        return $list;
    }

}