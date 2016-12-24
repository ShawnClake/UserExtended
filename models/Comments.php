<?php namespace Clake\Userextended\Models;

use Model;
use \October\Rain\Database\Traits\Encryptable;

use \October\Rain\Database\Traits\SoftDelete;

use Clake\UserExtended\Traits\Timezonable;

/**
 * TODO: Fill out fillable
 * TODO: Better documentation
 * TODO: Rename the model to get rid of the pluralization.
 */

/**
 * Class Comments
 * @package Clake\Userextended\Models
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
    protected $fillable = [
        'content',
        'user_id',
        'author_id',
    ];

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

    public function isAuthor($commentId, $userId)
    {
        $author = $this->id($commentId)->author();
        if($author == $userId)
            return true;
        return false;
    }

    public function isUser($commentId, $userId)
    {
        $user = $this->id($commentId)->user();
        if($user == $userId)
            return true;
        return false;
    }

    public function scopeId($query, $commentId)
    {
        return $query->where('id', $commentId);
    }

    public function scopeAuthor($query)
    {
        return $query->pluck('author_id');
    }

    public function scopeUser($query)
    {
        return $query->pluck('user_id');
    }

}