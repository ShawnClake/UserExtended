<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Friend;
use Auth;
use Illuminate\Support\Collection;
use RainLab\User\Models\User;

/**
 * TODO: Add a better method of testing for exists than the current methods. Follow DRY
 *          TODO: Maybe a class which takes two column names and then check them for the same data in reverse order
 * TODO: Add error checking in case user doesn't exist or no one is logged in
 */

/**
 * Class FriendsManager
 * @package Clake\UserExtended\Classes
 */
class FriendsManager
{

    /**
     * Returns a list of friend requests received.
     *
     * @param null $userid
     * @param int $limit
     * @return Collection
     */
    public static function listReceivedFriendRequests($userid = null, $limit = 5)
    {

        $users = new Collection();

        $limit = Helpers::unlimited($limit);

        /*$requests = Friends::where('user_that_accepted_request', $userid)->where('accepted', 0)->take($limit)->get();

        foreach ($requests as $request) {

            $u = User::where('id', $request['user_that_sent_request'])->get();
            $users->push($u[0]);

        }*/

        $requests = Friend::friendRequests()->take($limit)->get();

        foreach ($requests as $user) {
            $users->push(UserUtil::getUser($user->user_that_sent_request));
        }
        return $users;

    }

    /**
     * Soft deletes a friend. To readd a friend it will create a new record in the DB
     * This is useful to keep track of when, and how many times users deleted each other
     *
     * @param $friendUserID
     */
    public static function deleteFriend($friendUserID)
    {
        /*$exists = Friends::where('user_that_sent_request', self::getLoggedInUser()->id)->where('user_that_accepted_request', $friendUserID)->where('accepted', '1')->count();

        $exists2 = Friends::where('user_that_accepted_request', self::getLoggedInUser()->id)->where('user_that_sent_request', $friendUserID)->where('accepted', '1')->count();

        if($exists > 0)
        {
            $relation = Friends::where('user_that_sent_request', self::getLoggedInUser()->id)->where('user_that_accepted_request', $friendUserID)->where('accepted', '1')->first();
            $relation->delete();
        }

        if($exists2 > 0)
        {
            $relation = Friends::where('user_that_accepted_request', self::getLoggedInUser()->id)->where('user_that_sent_request', $friendUserID)->where('accepted', '1')->first();
            $relation->delete();
        }*/

        if(!self::isFriend($friendUserID))
            return;

        $relation = Friend::friend($friendUserID)->first();

        // Soft deletes arent working for some reason
        $relation->forceDelete();


    }

    /**
     * Sends a friend request
     *
     * @param $friendUserID
     */
    public static function sendFriendRequest($friendUserID)
    {

        /*
         // Anything except declined. Sent, Accepted, Blocked is not allowed states
        $exists = Friends::where('user_that_sent_request', self::getLoggedInUser()->id)->where('user_that_accepted_request', $friendUserID)->where('accepted', '<>', '2')->count();

        $exists2 = Friends::where('user_that_accepted_request', self::getLoggedInUser()->id)->where('user_that_sent_request', $friendUserID)->where('accepted', '<>', '2')->count();

        if($exists > 0 || $exists2 > 0)
            return;

        // If declined
        $exists = Friends::where('user_that_sent_request', self::getLoggedInUser()->id)->where('user_that_accepted_request', $friendUserID)->where('accepted', '2')->count();

        $exists2 = Friends::where('user_that_accepted_request', self::getLoggedInUser()->id)->where('user_that_sent_request', $friendUserID)->where('accepted', '2')->count();


        if($exists > 0)
        {
            $request = Friends::where('user_that_sent_request', self::getLoggedInUser()->id)->where('user_that_accepted_request', $friendUserID)->where('accepted', '2')->first();
        }
        else if($exists2 > 0)
        {
            $request = Friends::where('user_that_accepted_request', self::getLoggedInUser()->id)->where('user_that_sent_request', $friendUserID)->where('accepted', '2')->first();
        }
        else
        {
            $request = new Friends;
        }

        $request->user_that_sent_request = self::getLoggedInUser()->id;

        $request->user_that_accepted_request = $friendUserID;

        $request->accepted = 0;*/

        if(UserUtil::idIsLoggedIn($friendUserID))
            return;

        if(self::isFriend($friendUserID))
            return;

        if(Friend::isRelationExists($friendUserID) && !Friend::isDeclined($friendUserID))
            return;

        if(Friend::isDeclined($friendUserID))
        {
            $request = Friend::declined($friendUserID)->first();
        } else {
            $request = new Friend;
        }

        $request->addUsers(UserUtil::getUsersIdElseLoggedInUsersId(), $friendUserID);

        $request->setStatus(0);

        $request->save();

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
     * @param $userID1
     * @param null $userID2
     */
    public static function acceptRequest($userId1, $userId2 = null)
    {

        /*if($userID2 == null)
            $userID2 = self::getLoggedInUser()->id;

        $friends = Friends::where('user_that_sent_request', $userID1)->where('user_that_accepted_request', $userID2)->where('accepted', '0')->count();

        if($friends == 1)
        {

            $request = Friends::where('user_that_sent_request', $userID1)->where('user_that_accepted_request', $userID2)->where('accepted', '0')->get();
            $request = $request[0];
            $request->accepted = 1;
            $request->save();

        }*/

        if(!Friend::isRequested($userId1, $userId2))
            return;

        $request = Friend::request($userId1, $userId2)->first();

        $request->setStatus(1);

        $request->save();

    }

    /**
     * Declines a friend request from a user
     *
     * @param $userId1
     * @param null $userId2
     */
    public static function declineRequest($userId1, $userId2 = null)
    {
        /*if($userId2 == null)
            $userId2 = self::getLoggedInUser()->id;

        $relation = Friends::where('user_that_sent_request', $userId1)->where('user_that_accepted_request', $userId2)->where('accepted', '0')->first();

        $relation->accepted = 2;
        $relation->save();*/

        if(!Friend::isRequested($userId1, $userId2))
            return;

        $request = Friend::request($userId1, $userId2)->first();

        $request->setStatus(2);

        $request->save();

    }

    /**
     * Sets the relation between two users to blocked. Creates a new one if one already exists
     * @param $friendUserID
     */
    public static function blockFriend($friendUserID)
    {
        if(!Friend::isBlocked($friendUserID))
            return;

        if(Friend::isRelationExists($friendUserID))
            $relation = Friend::relation($friendUserID)->first();
        else
            $relation = new Friend();

        $relation->addUsers(UserUtil::getUsersIdElseLoggedInUsersId(), $friendUserID);

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
        /*$userid = self::getLoggedInUser()->id;

        $usersa = new Collection;

        $usersb = new Collection;

        $friendsa = Friends::where('user_that_sent_request', $userid)->where('accepted', '0')->take($limit)->get();

        //$friendsa = $friendsa->keyBy('user_that_accepted_request');

        //$friendsa = $friendsa->keyBy(function($item) { return "U" . $item['user_that_accepted_request']; });

        foreach ($friendsa as $result) {

            $u = User::where('id', $result['user_that_accepted_request'])->get();
            $usersa->push($u[0]);

        }

        $friendsb = Friends::where('user_that_accepted_request', $userid)->where('accepted', '0')->take($limit)->get();

        //$friendsb = $friendsb->keyBy('user_that_sent_request');

        //$friendsb = $friendsb->keyBy(function($item) { return "U" . $item['user_that_sent_request']; });

        foreach ($friendsb as $result) {

            $u = User::where('id', $result['user_that_sent_request'])->get();
            $usersb->push($u[0]);

        }

        $users = $usersa->merge($usersb);

        $users = $users->shuffle();

        $users = $users->take($limit);

        return $users;*/

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

        /*$userid = self::getLoggedInUser()->id;

        $usersa = new Collection;

        $usersb = new Collection;

        $friendsa = Friends::where('user_that_sent_request', $userid)->where('accepted', '1')->take($limit)->get();

        //$friendsa = $friendsa->keyBy('user_that_accepted_request');

        //$friendsa = $friendsa->keyBy(function($item) { return "U" . $item['user_that_accepted_request']; });

        foreach ($friendsa as $result) {

            $u = User::where('id', $result['user_that_accepted_request'])->get();
            $usersa->push($u[0]);

        }

        $friendsb = Friends::where('user_that_accepted_request', $userid)->where('accepted', '1')->take($limit)->get();

        //$friendsb = $friendsb->keyBy('user_that_sent_request');

        //$friendsb = $friendsb->keyBy(function($item) { return "U" . $item['user_that_sent_request']; });

        foreach ($friendsb as $result) {

            $u = User::where('id', $result['user_that_sent_request'])->get();
            $usersb->push($u[0]);

        }

        $users = $usersa->merge($usersb);

        $users = $users->shuffle();

        $users = $users->take($limit);

        return $users;*/

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
        /*$userid = self::getLoggedInUser()->id;

        $usersa = new Collection;

        $usersb = new Collection;

        $friendsa = Friends::where('user_that_sent_request', $userid)->where('accepted', '1')->get();

        //$friendsa = $friendsa->keyBy('user_that_accepted_request');

        //$friendsa = $friendsa->keyBy(function($item) { return "U" . $item['user_that_accepted_request']; });

        foreach ($friendsa as $result) {

            $u = User::where('id', $result['user_that_accepted_request'])->get();
            $usersa->push($u[0]);

        }

        $friendsb = Friends::where('user_that_accepted_request', $userid)->where('accepted', '1')->get();

        //$friendsb = $friendsb->keyBy('user_that_sent_request');

        //$friendsb = $friendsb->keyBy(function($item) { return "U" . $item['user_that_sent_request']; });

        foreach ($friendsb as $result) {

            $u = User::where('id', $result['user_that_sent_request'])->get();
            $usersb->push($u[0]);

        }

        $users = $usersa->merge($usersb);

        return $users;*/

        return self::listFriends(0, $userId);
    }

}