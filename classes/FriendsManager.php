<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Friend;
use Auth;
use Illuminate\Support\Collection;
use RainLab\User\Models\User;

/**
 * User Extended by Shawn Clake
 * Class FriendsManager
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Classes
 */
class FriendsManager
{

    /**
     * Returns a list of friend requests received.
     * @param int $limit
     * @return Collection
     */
    public static function listReceivedFriendRequests($limit = 5)
    {

        $users = new Collection();

        $limit = Helpers::unlimited($limit);

        $requests = Friend::friendRequests()->take($limit)->get();

        foreach ($requests as $user) {
            $users->push(UserUtil::getUser($user->user_that_sent_request));
        }
        return $users;

    }

    /**
     * Soft deletes a friend. To read a friend it will create a new record in the DB
     * This is useful to keep track of when, and how many times users deleted each other
     * @param $friendUserId
     */
    public static function deleteFriend($friendUserId)
    {
        if(!self::isFriend($friendUserId))
            return;

        $relation = Friend::friend($friendUserId)->first();

        // Soft deletes aren't working for some reason
        $relation->forceDelete();
    }

    /**
     * Sends a friend request
     * @param $friendUserId
     */
    public static function sendFriendRequest($friendUserId)
    {
        if(UserUtil::idIsLoggedIn($friendUserId))
            return;

        if(self::isFriend($friendUserId))
            return;

        if(Friend::isRelationExists($friendUserId) && !Friend::isDeclined($friendUserId))
            return;

        if(Friend::isDeclined($friendUserId))
        {
            $request = Friend::declined($friendUserId)->first();
        } else {
            $request = new Friend;
        }

        $request->addUsers(UserUtil::getUsersIdElseLoggedInUsersId(), $friendUserId);

        $request->setStatus(0);

        $request->save();
				
		$data = ['user' => UserUtil::getLoggedInUser()->name,
		         'friend' => UserUtil::getUserForUserId($friendUserId)->name];
		
		Mail::send('clake.userextended::mail.recieved_friend_request', $data, function($message) {
            $message->to(UserUtil::getUserForUserId($friendUserId)->email, UserUtil::getUser($friendUserId)->name);
        });

    }

    /**
     * Returns whether or not a user is our friend
     * Leave the second parameter blank to user the logged in user
     * @param $userID1
     * @param null $userID2
     * @return bool
     */
    public static function isFriend($userID1, $userID2 = null)
    {
        return Friend::isFriends($userID1, $userID2);
    }

    /**
     * Accepts a friend request from a user
     * One user is UserID1
     * Other user is UserID2. Logged in user by default
     * @param $userId1
     * @param null $userId2
     */
    public static function acceptRequest($userId1, $userId2 = null)
    {
        if(!Friend::isRequested($userId1, $userId2))
            return;

        $request = Friend::request($userId1, $userId2)->first();

        $request->setStatus(1);

        $request->save();
    }

    /**
     * Declines a friend request from a user
     * @param $userId1
     * @param null $userId2
     */
    public static function declineRequest($userId1, $userId2 = null)
    {
        if(!Friend::isRequested($userId1, $userId2))
            return;

        $request = Friend::request($userId1, $userId2)->first();

        $request->setStatus(2);

        $request->save();
    }

    /**
     * Sets the relation between two users to blocked. Creates a new one if one already exists
     * @param $friendUserId
     */
    public static function blockFriend($friendUserId)
    {
        if(!Friend::isBlocked($friendUserId))
            return;

        if(Friend::isRelationExists($friendUserId))
            $relation = Friend::relation($friendUserId)->first();
        else
            $relation = new Friend();

        $relation->addUsers(UserUtil::getUsersIdElseLoggedInUsersId(), $friendUserId);

        $relation->setStatus(3);

        $relation->save();
    }

    /**
     * Returns a list of both sent and received friend requests for the logged in user
     * @param int $limit
     * @return Collection|static
     */
    public static function listRequests($limit = 100)
    {
        $users = new Collection();

        $limit = Helpers::unlimited($limit);

        $requests = Friend::friendRequests(null)->take($limit)->get();

        foreach ($requests as $user) {
            $users->push(UserUtil::getUser($user->id));
        }

        $requests = Friend::sentRequests(null)->take($limit)->get();

        foreach ($requests as $user) {
            $users->push(UserUtil::getUser($user->id));
        }

        $users = $users->shuffle();

        $users = $users->take($limit);

        return $users;
    }

    /**
     * Returns the list of friends for the logged in user with a limit
     * @param int $limit
     * @param null $userId
     * @return Collection|static
     */
    public static function listFriends($limit = 0, $userId = null)
    {
        $users = new Collection();

        $limit = Helpers::unlimited($limit);

        $requests = Friend::friends($userId)->get();

        if($requests->isEmpty())
        {
            return $users;
        }

        foreach ($requests as $user) {
            $users->push(UserUtil::getUser($user->otherUser($userId)));
        }

        $users = $users->shuffle();

        $users = $users->take($limit);

        return $users;
    }

    /**
     * Returns all friends for the logged in user
     * @param null $userId
     * @return FriendsManager|Collection
     */
    public static function getAllFriends($userId = null)
    {
        return self::listFriends(0, $userId);
    }

}