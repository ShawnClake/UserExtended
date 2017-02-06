<?php namespace Clake\Userextended\Models;

use Model;
use \October\Rain\Database\Traits\Encryptable;

use \October\Rain\Database\Traits\SoftDelete;

use Clake\UserExtended\Traits\Timezonable;

/**
 * Class Comments
 * @package Clake\Userextended\Models
 * @method static Comment id($commentId) Query
 * @method static Comment author() Query
 * @method static Comment user() Query
 */
class Comment extends Model
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

    /**
     * Determines whether or not the passed in userId is the writer of the comment
     * @param $commentId
     * @param $userId
     * @return bool
     */
    public function isAuthor($commentId, $userId)
    {
        $author = $this->id($commentId)->author();
        if($author == $userId)
            return true;
        return false;
    }

    /**
     * Returns whether the passed in user is the recipient of a comment
     * @param $commentId
     * @param $userId
     * @return bool
     */
    public function isUser($commentId, $userId)
    {
        $user = $this->id($commentId)->user();
        if($user == $userId)
            return true;
        return false;
    }

    /**
     * Gets a comment based upon a passed in commentId
     * @param $query
     * @param $commentId
     * @return mixed
     */
    public function scopeId($query, $commentId)
    {
        return $query->where('id', $commentId);
    }

    /**
     * Gets a comment's author
     * @param $query
     * @return mixed
     */
    public function scopeAuthor($query)
    {
        return $query->pluck('author_id');
    }

    /**
     * Gets a comments recipient
     * @param $query
     * @return mixed
     */
    public function scopeUser($query)
    {
        return $query->pluck('user_id');
    }

}