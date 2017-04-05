<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Friend;
use Auth;
use Illuminate\Support\Collection;
use RainLab\User\Models\User;
use Mail;
use Log;

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
     * These states are for 2.2.00 and don't function as part of the current release.
     */
    const UE_RELATION_STATES = [
        '1'       => 'UE_FRIEND_REQUESTED',
        '2'       => 'UE_FOLLOWING',
        '4'       => 'UE_SUBSCRIBED',
        '8'       => 'UE_FRIENDS',
        '16'      => 'UE_DECLINED',
        '1048576' => 'UE_BLOCKED',
        '2097152' => 'UE_DELETED',
    ];

    const UE_FRIEND_REQUESTED = 1; // 0 digit
    const UE_FOLLOWING = 2; // 1 digit
    const UE_SUBSCRIBED = 4; // 2 digit
    const UE_FRIENDS = 8; // 3 digit
    const UE_DECLINED = 16; // 4 digit

    // Additional Bond States should be given here. States 0-9 are reserved for UE official
    // States 10-19 can be used by other modules

    const UE_BLOCKED = 1048576; // 20 digit
    const UE_DELETED = 2097152; // 21 digit

    // States above 21 will override UE_BLOCKED and UE_DELETED. Be extremely careful with this!!!


    const UE_RELATION_SENDER = 'user_that_sent_request';
    const UE_RELATION_RECEIVER = 'user_that_accepted_request';
    /**
     * Returns a list of friend requests received.
     * @param int $limit
     * @return Collection
     */
    public static function listReceivedFriendRequests($limit = 5)
    {
        $users = new Collection();
		
		$limit = Helpers::unlimited($limit);
		
        $requests = Friend::friendRequests()->take($limit);
		
        foreach ($requests as $user) {
            $users->push(UserUtil::getRainlabUser($user->user_that_sent_request));
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

		Log::info(UserUtil::getLoggedInUser()->name . " deleted " . UserUtil::getUserForUserId($friendUserId)->name . " as a friend.");
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

        $user = UserUtil::getLoggedInUser();
        if(!isset($user))
            return;

        if(self::isFriend($friendUserId))
            return;

        if(Friend::isRelationExists($friendUserId))
            return;

        if(Friend::isDeclined($friendUserId))
        {
            $request = Friend::declined($friendUserId)->first();
        } else {
            $request = new Friend;
        }

        $request->addUsers(UserUtil::getUsersIdElseLoggedInUsersId(), $friendUserId);

        $request->setExclusiveBond(FriendsManager::UE_FRIEND_REQUESTED);

        $request->save();
				
		$data = ['user' => UserUtil::getLoggedInUser()->name,
		         'friend' => UserUtil::getUserForUserId($friendUserId)->name];
		
		
		Log::info(UserUtil::getLoggedInUser()->name . " sent " . UserUtil::getUser($friendUserId)->name . " a friend request.");
		
		Mail::send('clake.userextended::mail.received_friend_request', $data, function($message) use ($friendUserId) {
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
     * Returns whether or not the two users have a friend request between them. 
     * Leave the second parameter blank to user the logged in user
     * @param $userID1
     * @param null $userID2
     * @return bool
     */
	public static function isRequested($userID1, $userID2 = null)
    {
		return Friend::isRequested($userID1, $userID2);
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

        /** @var Friend $request */
        $request = Friend::request($userId1, $userId2)->first();

        $request->setExclusiveBond(FriendsManager::UE_FRIENDS);
		
		Log::info(UserUtil::getUserForUserId($userId2)->name . " accepted " . UserUtil::getUserForUserId($userId1)->name . "'s friend request.");
		
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

        /** @var Friend $request */
        $request = Friend::request($userId1, $userId2)->first();

        $request->setExclusiveBond(FriendsManager::UE_DECLINED);

		Log::info(UserUtil::getUserForUserId($userId2)->name . " declined " . UserUtil::getUserForUserId($userId1)->name . "'s friend request.");
		
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

        $relation->setExclusiveBond(FriendsManager::UE_BLOCKED);

		Log::info(UserUtil::getLoggedInUser() . " blocked " . UserUtil::getUserForUserId($friendUserId)->name . ".");
		
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

        $requests = Friend::friendRequests(null)->take($limit);

        foreach ($requests as $user) {
            $users->push(UserUtil::getUser($user->id));
        }

        $requests = Friend::sentRequests(null)->take($limit);

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
        //dd(json_encode(Friend::friends($userId)));
        $requests = Friend::friends($userId);
        //dd($requests->get());
        if($requests->isEmpty())
        {
            return $users;
        }

        foreach ($requests as $user) {
            $users->push(UserUtil::getRainlabUser($user->otherUser($userId)));
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