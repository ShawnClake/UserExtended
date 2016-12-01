<?php namespace Clake\Userextended\Models;

use Model;

/**
 * Timezone Model
 */
class Timezone extends Model
{

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

}