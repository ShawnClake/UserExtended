<?php namespace Clake\Userextended\Models;

use Clake\UserExtended\Classes\UserUtil;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use Clake\UserExtended\Traits\Timezonable;

/**
 * User Extended by Shawn Clake
 * Class Friends
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Models
 *
 * @method static Friend friend($userIdA, $userIdB = null) Query
 * @method static Friend request($userIdA, $userIdB = null) Query
 * @method static Friend declined($userIdA, $userIdB = null) Query
 * @method static Friend blocked($userIdA, $userIdB = null) Query
 * @method static Friend relation($userIdA, $userIdB = null) Query
 * @method static Friend friendRequests($userId = null) Query
 * @method static Friend sentRequests($userId = null) Query
 * @method static Friend friends($userId = null) Query
 * @method static Friend blocks($userId = null) Query
 * @method static Friend pluckSender() Query
 * @method static Friend pluckReceiver() Query
 * @method static Friend sender($userId = null) Query
 * @method static Friend receiver($userId = null) Query
 * @method static Friend notMe($userId = null) Query
 */
class Friend extends Model
{

    use SoftDelete;

    use Timezonable;

    private $statuses = [
        'requested' => 0,
        'accepted' => 1,
        'declined' => 2,
        'blocked' => 3,
    ];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'clake_userextended_friends';

    protected $dates = ['deleted_at'];

    protected $timezonable = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'user_that_sent_request',
        'user_that_accepted_request',
        'accepted',
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

    /**
     * Returns whether or not two users are friends
     *
     * @param $userIdA
     * @param $userIdB
     * @return bool
     */
    public static function isFriends($userIdA, $userIdB = null)
    {
        if(Friend::friend($userIdA, $userIdB)->count() > 0)
            return true;
        return false;
    }

    /**
     * @param $userIdA
     * @param null $userIdB
     * @return bool
     */
    public static function isRequested($userIdA, $userIdB = null)
    {

        if(Friend::request($userIdA, $userIdB)->count() > 0)
            return true;
        return false;
    }

    /**
     * @param $userIdA
     * @param null $userIdB
     * @return bool
     */
    public static function isDeclined($userIdA, $userIdB = null)
    {
        if(Friend::declined($userIdA, $userIdB)->count() > 0)
            return true;
        return false;
    }

    /**
     * @param $userIdA
     * @param null $userIdB
     * @return bool
     */
    public static function isBlocked($userIdA, $userIdB = null)
    {
        if(Friend::blocked($userIdA, $userIdB)->count() > 0)
            return true;
        return false;
    }

    /**
     * @param $userIdA
     * @param null $userIdB
     * @return bool
     */
    public static function isRelationExists($userIdA, $userIdB = null)
    {
        if(Friend::relation($userIdA, $userIdB)->count() > 0)
            return true;
        return false;
    }

    /**
     * Determines whether or not two users are friends. Run count on the result. If >0 then they are.
     * Can find sender and accepter by examining the returned object using ->get() on the result
     * @param $query
     * @param $userIdA
     * @param $userIdB
     * @return mixed
     */
    public function scopeFriend($query, $userIdA, $userIdB = null)
    {

        $userIdB = UserUtil::getUsersIdElseLoggedInUsersId($userIdB);
        if($userIdB == null)
            return $query;

        return $query->where(function ($query) use($userIdA, $userIdB){
            $query->where('user_that_sent_request', $userIdA)
                ->where('user_that_accepted_request', $userIdB)
                ->where('accepted', '1');
        })->orWhere(function ($query) use($userIdA, $userIdB){
            $query->where('user_that_sent_request', $userIdB)
                ->where('user_that_accepted_request', $userIdA)
                ->where('accepted', '1');
        });

    }

    /**
     * Checks whether or not a friend request between two users exists
     * Can find sender and accepter by examining the returned object using ->get() on the result
     * @param $query
     * @param $userIdA
     * @param null $userIdB
     * @return mixed
     */
    public function scopeRequest($query, $userIdA, $userIdB = null)
    {

        $userIdB = UserUtil::getUsersIdElseLoggedInUsersId($userIdB);
        if($userIdB == null)
            return $query;

        return $query->where(function ($query) use($userIdA, $userIdB) {
            $query->where('user_that_sent_request', $userIdA)
                ->where('user_that_accepted_request', $userIdB)
                ->where('accepted', '0');
        })->orWhere(function ($query) use($userIdA, $userIdB) {
            $query->where('user_that_sent_request', $userIdB)
                ->where('user_that_accepted_request', $userIdA)
                ->where('accepted', '0');
        });

    }

    /**
     * Checks whether or not a friend request between users was declined. Run count on result, if >0 then they are.
     * Can find sender and accepter by examining the returned object using ->get() on the result
     * @param $query
     * @param $userIdA
     * @param null $userIdB
     * @return mixed
     */
    public function scopeDeclined($query, $userIdA, $userIdB = null)
    {

        $userIdB = UserUtil::getUsersIdElseLoggedInUsersId($userIdB);
        if($userIdB == null)
            return $query;

        return $query->where(function ($query) use($userIdA, $userIdB) {
            $query->where('user_that_sent_request', $userIdA)
                ->where('user_that_accepted_request', $userIdB)
                ->where('accepted', '2');
        })->orWhere(function ($query) use($userIdA, $userIdB) {
            $query->where('user_that_sent_request', $userIdB)
                ->where('user_that_accepted_request', $userIdA)
                ->where('accepted', '2');
        });
    }

    /**
     * Scopes to the relation where two users are blocked
     * @param $query
     * @param $userIdA
     * @param null $userIdB
     * @return mixed
     */
    public function scopeBlocked($query, $userIdA, $userIdB = null)
    {

        $userIdB = UserUtil::getUsersIdElseLoggedInUsersId($userIdB);
        if($userIdB == null)
            return $query;

        return $query->where(function ($query) use($userIdA, $userIdB) {
            $query->where('user_that_sent_request', $userIdA)
                ->where('user_that_accepted_request', $userIdB)
                ->where('accepted', '3');
        })->orWhere(function ($query) use($userIdA, $userIdB) {
            $query->where('user_that_sent_request', $userIdB)
                ->where('user_that_accepted_request', $userIdA)
                ->where('accepted', '3');
        });
    }

    /**
     * Scopes to the relation between two users
     * @param $query
     * @param $userIdA
     * @param null $userIdB
     * @return mixed
     */
    public function scopeRelation($query, $userIdA, $userIdB = null)
    {
        $userIdB = UserUtil::getUsersIdElseLoggedInUsersId($userIdB);
        if($userIdB == null)
            return $query;

        return $query->where(function ($query) use($userIdA, $userIdB) {
            $query->where('user_that_sent_request', $userIdA)
                ->where('user_that_accepted_request', $userIdB);
        })->orWhere(function ($query) use($userIdA, $userIdB) {
            $query->where('user_that_sent_request', $userIdB)
                ->where('user_that_accepted_request', $userIdA);
        });
    }

    /**
     * Scopes to friend requests received by the passed in user. Takes logged in user if none are passed.
     * @param $query
     * @param null $userId
     * @return mixed
     */
    public function scopeFriendRequests($query, $userId = null)
    {
        $userId = UserUtil::getUsersIdElseLoggedInUsersId($userId);
        if($userId == null)
            return $query;

        return $query->where('user_that_accepted_request', $userId)->where('accepted', '0');
    }

    /**
     * Scopes to friend requests sent by the passed in user. Takes logged in user if none are passed.
     * @param $query
     * @param null $userId
     * @return mixed
     */
    public function scopeSentRequests($query, $userId = null)
    {
        $userId = UserUtil::getUsersIdElseLoggedInUsersId($userId);
        if($userId == null)
            return $query;

        return $query->where('user_that_sent_request', $userId)->where('accepted', '0');
    }

    /**
     * Scopes to the friends the passed in user has. Takes logged in user if none are passed.
     * @param $query
     * @param null $userId
     * @return mixed
     */
    public function scopeFriends($query, $userId = null)
    {
        $userId = UserUtil::getUsersIdElseLoggedInUsersId($userId);
        if($userId == null)
            return $query;

        return $query->where(function ($query) use ($userId){
            $query->where('user_that_sent_request', $userId)
                ->where('accepted', '1');
        })->orWhere(function ($query) use ($userId) {
                $query->where('user_that_accepted_request', $userId)
                ->where('accepted', '1');
        });

    }

    /**
     * Scopes to users who are 'blocked' from each other
     * @param $query
     * @param null $userId
     * @return mixed
     */
    public function scopeBlocks($query, $userId = null)
    {
        $userId = UserUtil::getUsersIdElseLoggedInUsersId($userId);
        if($userId == null)
            return $query;

        return $query->where(function ($query) use ($userId) {
            $query->where('user_that_sent_request', $userId)
                ->where('accepted', '3');
        })->orWhere(function ($query) use ($userId) {
            $query->where('user_that_accepted_request', $userId)
                ->where('accepted', '3');
        });
    }

    /**
     * Will retrieve only the sender
     * @param $query
     * @return mixed
     */
    public function scopePluckSender($query)
    {
        return $query->pluck('user_that_sent_request');
    }

    /**
     * Will retrieve only the receiver
     * @param $query
     * @return mixed
     */
    public function scopePluckReceiver($query)
    {
        return $query->pluck('user_that_accepted_request');
    }

    /**
     * Checks whether the sender is the user specified
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeSender($query, $userId = null)
    {
        $userId = UserUtil::getUsersIdElseLoggedInUsersId($userId);
        if($userId == null)
            return $query;

        return $query->where('user_that_sent_request', $userId);
    }

    /**
     * Checks whether the receiver is the user specified
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeReceiver($query, $userId = null)
    {
        $userId = UserUtil::getUsersIdElseLoggedInUsersId($userId);
        if($userId == null)
            return $query;

        return $query->where('user_that_accepted_request', $userId);
    }

    /**
     * Easy method for adding sender and receiver or either or to the model
     * Also handles resetting these fields if need be by leaving params blank
     * @param null $sender
     * @param null $receiver
     * @return $this
     */
    public function addUsers($sender = null, $receiver = null)
    {
        if($sender != null)
            $this->user_that_sent_request = $sender;

        if($receiver != null)
            $this->user_that_accepted_request = $receiver;

        return $this;
    }

    /**
     * Swaps the sender and receiver fields of the model
     * @return Friends
     */
    public function swapUsers()
    {
        $sender = $this->user_that_sent_request;
        $receiver = $this->user_that_accepted_request;

        return $this->addUsers($receiver, $sender);
    }

    /**
     * Sets the accepted field in the model.
     * 0 => Request sent
     * 1 => Friend Accepted
     * 2 => Friend Declined
     * 3 => Blocked
     * @param $status
     */
    public function setStatus($status)
    {
        $this->accepted = $status;
    }

    /**
     * Returns the other user ID in a row
     * @param $query
     * @param null $userId
     * @return mixed
     */
    public function scopeNotMe($query, $userId = null)
    {
        $userId = UserUtil::getUsersIdElseLoggedInUsersId($userId);
        if($userId == null)
            return $query;

        $testa = $query;

        $sender = $testa->pluck('user_that_sent_request');

        if($sender === UserUtil::getUsersIdElseLoggedInUsersId())
            return $query->pluck('user_that_accepted_request');

        return $query->pluck('user_that_sent_request');
    }

    /**
     * Returns the other user ID in a row
     * @param null $userId
     */
    public function otherUser($userId = null)
    {
        $userId = UserUtil::getUsersIdElseLoggedInUsersId($userId);
        if($userId == null)
            return;

        if($this->user_that_sent_request == $userId)
            return $this->user_that_accepted_request;
        else
            return $this->user_that_sent_request;

    }

}