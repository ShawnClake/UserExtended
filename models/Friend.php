<?php namespace Clake\Userextended\Models;

use Clake\UserExtended\Classes\FriendsManager;
use Clake\UserExtended\Classes\Helpers;
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
    public $belongsTo = [
        'sender'   => ['Clake\UserExtended\Models\UserExtended', 'key' => 'user_that_sent_request', 'otherKey' => 'id'],
        'acceptor' => ['Clake\UserExtended\Models\UserExtended', 'key' => 'user_that_accepted_request', 'otherKey' => 'id'],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * Checks whether a relation exists, if it does, don't create another
     * @return bool
     */
    public function beforeCreate()
    {
        if(self::isRelationExists($this->user_that_sent_request, $this->user_that_accepted_request))
        {
            return false;
        }
    }

    /**
     * Returns the highest priority set relation, this is useful in cases where we need to utilize overrides
     * @param $userIdA
     * @param null $userIdB
     * @return array|int
     */
    public static function getHighestRelation($userIdA, $userIdB = null)
    {
        $relation = Friend::relation($userIdA, $userIdB);
        if($relation->count() == 0)
            return [];

        $hiBit = Helpers::hiBit($relation->first()->relation);
        if(key_exists($hiBit, FriendsManager::$UE_RELATION_STATES))
            return $hiBit;

        return 0;
    }

    /**
     * Returns a collection of all the relations a user has with another user
     * @param $userIdA
     * @param null $userIdB
     * @return array
     */
    public static function getAllRelations($userIdA, $userIdB = null)
    {
        $relation = Friend::relation($userIdA, $userIdB);
        if($relation->count() == 0)
            return [];

        $bits = $relation->first()->relation;

        $relations = [];

        foreach(FriendsManager::$UE_RELATION_STATES as $bit => $state)
        {
            if(Helpers::isBitSet($bits, $bit))
                $relations[] = $bit;
        }

        return $relations;
    }

    /**
     * Returns whether or not two users share a specific bond between them
     * @param $bondType
     * @param $userIdA
     * @param null $userIdB
     * @return bool
     */
    public static function isBond($bondType, $userIdA, $userIdB = null)
    {
        return in_array($bondType, self::getAllRelations($userIdA, $userIdB));
    }

    /**
     * Returns whether or not two users are friends
     * @param $userIdA
     * @param $userIdB
     * @return bool
     */
    public static function isFriends($userIdA, $userIdB = null)
    {
        return in_array(FriendsManager::UE_FRIENDS, self::getAllRelations($userIdA, $userIdB));
    }

    /**
     * Returns whether or not two users have a friend request between them
     * @param $userIdA
     * @param null $userIdB
     * @return bool
     */
    public static function isRequested($userIdA, $userIdB = null)
    {
        return in_array(FriendsManager::UE_FRIEND_REQUESTED, self::getAllRelations($userIdA, $userIdB));
    }

    /**
     * Returns whether or not two users have a declined friend request between them.
     * @param $userIdA
     * @param null $userIdB
     * @return bool
     */
    public static function isDeclined($userIdA, $userIdB = null)
    {
        return in_array(FriendsManager::UE_DECLINED, self::getAllRelations($userIdA, $userIdB));
    }

    /**
     * Returns whether or not two users are blocked from each other
     * @param $userIdA
     * @param null $userIdB
     * @return bool
     */
    public static function isBlocked($userIdA, $userIdB = null)
    {
        return in_array(FriendsManager::UE_BLOCKED, self::getAllRelations($userIdA, $userIdB));
    }

    /**
     * Returns whether or not two users share a relation or bond
     * @param $userIdA
     * @param null $userIdB
     * @return bool
     */
    public static function isRelationExists($userIdA, $userIdB = null)
    {
        return !!(self::getHighestRelation($userIdA, $userIdB));
    }

    /**
     * Returns a relation model that has been filtered via a passed in closure function
     * @param $closure
     * @param $query
     * @param $userIdA
     * @param null $userIdB
     * @return array|mixed
     */
    private function filterRelation($closure, $query, $userIdA, $userIdB = null)
    {
        $userIdB = UserUtil::getUsersIdElseLoggedInUsersId($userIdB);
        if($userIdB == null)
            return $query;

        $query = $this->scopeRelation($query, $userIdA, $userIdB);

        if($query->count() == 0)
            return [];

        $relations = $query->get();

        return $relations->reject($closure);
    }

    /**
     * Returns a collection of relation models which have been filtered via a passed in closure function
     * @param $closure
     * @param $query
     * @param $userId
     * @param string $direction
     * @return array
     */
    private function filterRelations($closure, $query, $userId, $direction = '')
    {
        $userId = UserUtil::getUsersIdElseLoggedInUsersId($userId);
        if($userId == null)
            return $query;

       if($direction == FriendsManager::UE_RELATION_SENDER)
            $query = $this->scopeSentRelations($query, $userId);
       else if($direction == FriendsManager::UE_RELATION_RECEIVER)
           $query = $this->scopeReceivedRelations($query, $userId);
       else
           $query = $this->scopeRelations($query, $userId);

        if($query->count() == 0)
            return [];

        $relations = $query->get();

        return $relations->reject($closure);
    }

    /**
     * Determines whether or not two users are friends. Run count on the result. If >0 then they are.
     * Can find sender and acceptor by examining the returned object using ->get() on the result
     * @param $query
     * @param $userIdA
     * @param $userIdB
     * @return mixed
     */
    public function scopeFriend($query, $userIdA, $userIdB = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_FRIENDS);
        };

        return $this->filterRelation($closure, $query, $userIdA, $userIdB);
    }

    /**
     * Checks whether or not a friend request between two users exists
     * Can find sender and acceptor by examining the returned object using ->get() on the result
     * @param $query
     * @param $userIdA
     * @param null $userIdB
     * @return mixed
     */
    public function scopeRequest($query, $userIdA, $userIdB = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_FRIEND_REQUESTED);
        };

        return $this->filterRelation($closure, $query, $userIdA, $userIdB);
    }

    /**
     * Checks whether or not a friend request between users was declined. Run count on result, if >0 then they are.
     * Can find sender and acceptor by examining the returned object using ->get() on the result
     * @param $query
     * @param $userIdA
     * @param null $userIdB
     * @return mixed
     */
    public function scopeDeclined($query, $userIdA, $userIdB = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_DECLINED);
        };

        return $this->filterRelation($closure, $query, $userIdA, $userIdB);
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
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_BLOCKED);
        };

        return $this->filterRelation($closure, $query, $userIdA, $userIdB);
    }

    /**
     * Scopes to the relation where a user is following another
     * @param $query
     * @param $userIdA
     * @param null $userIdB
     * @return array|mixed
     */
    public function scopeFollow($query, $userIdA, $userIdB = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_FOLLOWING);
        };

        return $this->filterRelation($closure, $query, $userIdA, $userIdB);
    }

    /**
     * Scopes to the relation where a user is subscribed to another
     * @param $query
     * @param $userIdA
     * @param null $userIdB
     * @return array|mixed
     */
    public function scopeSubscription($query, $userIdA, $userIdB = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_SUBSCRIBED);
        };

        return $this->filterRelation($closure, $query, $userIdA, $userIdB);
    }

    /**
     * Scopes to all relations with a user involved
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeRelations($query, $userId)
    {
        return $query->where(FriendsManager::UE_RELATION_RECEIVER, $userId)
            ->orWhere(FriendsManager::UE_RELATION_SENDER, $userId);
    }

    /**
     * Scopes to all relations where a user initiated the relation
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeSentRelations($query, $userId)
    {
        return $query->where(FriendsManager::UE_RELATION_SENDER, $userId);
    }

    /**
     * Scopes to all relaitons where a user received the relation
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeReceivedRelations($query, $userId)
    {
        return $query->where(FriendsManager::UE_RELATION_RECEIVER, $userId);
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
            $query->where(FriendsManager::UE_RELATION_SENDER, $userIdA)
                ->where(FriendsManager::UE_RELATION_RECEIVER, $userIdB);
        })->orWhere(function ($query) use($userIdA, $userIdB) {
            $query->where(FriendsManager::UE_RELATION_SENDER, $userIdB)
                ->where(FriendsManager::UE_RELATION_RECEIVER, $userIdA);
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
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_FRIEND_REQUESTED);
        };

        return $this->filterRelations($closure, $query, $userId, FriendsManager::UE_RELATION_RECEIVER);
    }

    /**
     * Scopes to friend requests sent by the passed in user. Takes logged in user if none are passed.
     * @param $query
     * @param null $userId
     * @return mixed
     */
    public function scopeSentRequests($query, $userId = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_FRIENDS);
        };

        return $this->filterRelation($closure, $query, $userId, FriendsManager::UE_RELATION_SENDER);
    }

    /**
     * Scopes to the friends the passed in user has. Takes logged in user if none are passed.
     * @param $query
     * @param null $userId
     * @return mixed
     */
    public function scopeFriends($query, $userId = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_FRIENDS);
        };

        return $this->filterRelations($closure, $query, $userId);
    }

    /**
     * Scopes to users who are 'blocked' from each other
     * @param $query
     * @param null $userId
     * @return mixed
     */
    public function scopeBlocks($query, $userId = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_BLOCKED);
        };

        return $this->filterRelations($closure, $query, $userId);
    }

    /**
     * Scopes to a users followers
     * @param $query
     * @param null $userId
     * @return array
     */
    public function scopeFollowers($query, $userId = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_FOLLOWING);
        };

        return $this->filterRelations($closure, $query, $userId, FriendsManager::UE_RELATION_RECEIVER);
    }

    /**
     * Scopes to a collection of users a user is following
     * @param $query
     * @param null $userId
     * @return array
     */
    public function scopeFollowing($query, $userId = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_FOLLOWING);
        };

        return $this->filterRelations($closure, $query, $userId, FriendsManager::UE_RELATION_SENDER);
    }

    /**
     * Scopes to a collection of a users subscribers
     * @param $query
     * @param null $userId
     * @return array
     */
    public function scopeSubscribers($query, $userId = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_SUBSCRIBED);
        };

        return $this->filterRelations($closure, $query, $userId, FriendsManager::UE_RELATION_RECEIVER);
    }

    /**
     * Scopes to a collection of a users subscriptions
     * @param $query
     * @param null $userId
     * @return array
     */
    public function scopeSubscriptions($query, $userId = null)
    {
        $closure = function ($relation) {
            return !$relation->hasBond(FriendsManager::UE_SUBSCRIBED);
        };

        return $this->filterRelations($closure, $query, $userId, FriendsManager::UE_RELATION_SENDER);
    }

    /**
     * Will retrieve only the sender
     * @param $query
     * @return mixed
     */
    public function scopePluckSender($query)
    {
        return $query->pluck(FriendsManager::UE_RELATION_SENDER);
    }

    /**
     * Will retrieve only the receiver
     * @param $query
     * @return mixed
     */
    public function scopePluckReceiver($query)
    {
        return $query->pluck(FriendsManager::UE_RELATION_RECEIVER);
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

        return $query->where(FriendsManager::UE_RELATION_SENDER, $userId);
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

        return $query->where(FriendsManager::UE_RELATION_RECEIVER, $userId);
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
     * @deprecated
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

    /**
     * Sets bonds between two users
     * @param $relation_states
     */
    public function setBond($relation_states)
    {
        if(!is_array($relation_states))
            $relation_states = [$relation_states];

        foreach($relation_states as $state)
        {
            $this->relation = (int)($this->relation) | (int)($state);
        }
    }

    /**
     * Determines whether a bond exists between two users
     * @param $relation_state
     * @return bool
     */
    public function hasBond($relation_state)
    {
        if(!!((int)($this->relation) & (int)($relation_state)))
            return true;
        return false;
    }

    /**
     * Removes a bond between two users
     * @param $relation_states
     */
    public function removeBond($relation_states)
    {
        if(!is_array($relation_states))
            $relation_states = [$relation_states];

        foreach($relation_states as $state)
        {
            $this->relation = (int)($this->relation) & ~((int)($state));
        }
    }

    /**
     * Flushes all bonds
     * Removes any relation between two users
     */
    public function flushBonds()
    {
        $this->relation = 0;
    }

    /**
     * Flushes all bonds and then sets the bonds passed in
     * @param $relation_states
     */
    public function setExclusiveBond($relation_states)
    {
        $this->flushBonds();
        $this->setBond($relation_states);
    }

    /**
     * Returns all of the bond states which exist in the project
     * @return array
     */
    public function getBondStates()
    {
        return FriendsManager::$UE_RELATION_STATES;
    }

}