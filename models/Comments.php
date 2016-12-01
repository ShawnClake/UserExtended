<?php namespace Clake\Userextended\Models;

use Model;
use \October\Rain\Database\Traits\Encryptable;

use \October\Rain\Database\Traits\SoftDelete;

use Clake\UserExtended\Traits\Timezonable;

/**
 * Comments Model
 */
class Comments extends Model
{

    use Encryptable;

    use SoftDelete;

    use Timezonable;

    protected $encryptable = ['content'];

    protected $dates = ['deleted_at'];

    protected $timezonable = [

        'created_at',
        'updated_at'

    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'clake_userextended_comments';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [
        'user' => ['Clake\Userextended\Models\UserExtended', 'key' => 'user_id'],
        'author' => ['Clake\Userextended\Models\UserExtended', 'key' => 'author_id'],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}