<?php namespace Clake\Userextended\Models;

use Model;

/**
 * UsersGroups Model
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
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}