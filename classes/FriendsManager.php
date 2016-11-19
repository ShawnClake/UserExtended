<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Friends;
use Auth;
use Illuminate\Support\Collection;
use RainLab\User\Models\User;

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
    public static function listMyReceivedFriendRequests($userid = null, $limit = 5)
    {

        $users = new Collection();

        if($userid == null)
            $userid = self::getLoggedInUser()->id;

        $requests = Friends::where('user_that_accepted_request', $userid)->where('accepted', 0)->take($limit)->get();

        foreach ($requests as $request) {

            $u = User::where('id', $request['user_that_sent_request'])->get();
            $users->push($u[0]);

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
        $exists = Friends::where('user_that_sent_request', self::getLoggedInUser()->id)->where('user_that_accepted_request', $friendUserID)->where('accepted', '1')->count();

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
        }

    }

    /**
     * Sends a friend request
     *
     * @param $friendUserID
     */
    public static function sendFriendRequest($friendUserID)
    {

        $exists = Friends::where('user_that_sent_request', self::getLoggedInUser()->id)->where('user_that_accepted_request', $friendUserID)->where('accepted', '<>', '2')->count();

        $exists2 = Friends::where('user_that_accepted_request', self::getLoggedInUser()->id)->where('user_that_sent_request', $friendUserID)->where('accepted', '<>', '2')->count();

        if($exists > 0 || $exists2 > 0)
            return;

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

        $request->accepted = 0;

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

        if($userID2 == null)
            $userID2 = self::getLoggedInUser()->id;

        $friendsa = Friends::where('user_that_sent_request', $userID1)->where('user_that_accepted_request', $userID2)->where('accepted', '1')->count();

        if($friendsa > 0)
            return true;

        $friendsb = Friends::where('user_that_sent_request', $userID2)->where('user_that_accepted_request', $userID1)->where('accepted', '1')->count();

        if($friendsb > 0)
            return true;

        return false;

    }

    /**
     * Accepts a friend request from a user
     *
     * @param $userID1
     * @param null $userID2
     */
    public static function acceptRequest($userID1, $userID2 = null)
    {

        if($userID2 == null)
            $userID2 = self::getLoggedInUser()->id;

        $friends = Friends::where('user_that_sent_request', $userID1)->where('user_that_accepted_request', $userID2)->where('accepted', '0')->count();

        if($friends == 1)
        {

            $request = Friends::where('user_that_sent_request', $userID1)->where('user_that_accepted_request', $userID2)->where('accepted', '0')->get();
            $request = $request[0];
            $request->accepted = 1;
            $request->save();

        }

    }

    /**
     * Declines a friend request from a user
     *
     * @param $userId1
     * @param null $userId2
     */
    public static function declineRequest($userId1, $userId2 = null)
    {
        if($userId2 == null)
            $userId2 = self::getLoggedInUser()->id;

        $relation = Friends::where('user_that_sent_request', $userId1)->where('user_that_accepted_request', $userId2)->where('accepted', '0')->first();

        $relation->accepted = 2;
        $relation->save();

    }

    /**
     * Lists friend requests both received and sent
     *
     * @param int $limit
     * @return static
     */
    public static function listRequests($limit = 100)
    {
        $userid = self::getLoggedInUser()->id;

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

        return $users;
    }

    /**
     * Returns a list of friends with a limit
     *
     * @param $limit
     * @return static
     */
    public static function listFriends($limit)
    {

        $userid = self::getLoggedInUser()->id;

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

        return $users;

    }

    /**
     * Returns all friends
     *
     * @return Collection
     */
    public static function getAll()
    {
        $userid = self::getLoggedInUser()->id;

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

        return $users;
    }

    /**
     * Returns the logged in user model
     *
     * @return User
     */
    private static function getLoggedInUser()
    {
        if (!$user = Auth::getUser()) {
            return null;
        }

        $user->touchLastSeen();

        return $user;
    }
}