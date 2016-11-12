<?php

namespace Clake\UserExtended\Classes;
use Illuminate\Support\Collection;
use RainLab\User\Models\User;

/**
 * Class UserManager
 * @package Clake\UserExtended\Classes
 */
class UserManager
{
    /**
     * Returns a random set of users. This won't return users in our friends list.
     * @param int $limit
     * @return Collection
     */
    public static function getRandomUserSet($limit = 5)
    {

        $returner = new Collection;

        $userCount = User::all()->count();

        if($userCount < $limit)
            $limit = $userCount;

        $users = User::all()->random($limit);

        $friends = FriendsManager::getAll();

        foreach($users as $user)
        {

            $userAdd = true;

            foreach($friends as $friend)
            {

                if($user->id == $friend->id)
                {
                    $userAdd = false;
                    break;
                }

            }

            if($user->id == UserUtil::getLoggedInUser()->id)
                $userAdd = false;

            if($userAdd)
            {
                $returner->push($user);
            }

        }

        return $returner;

    }
}