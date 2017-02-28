<?php namespace Clake\UserExtended\Classes;

use Illuminate\Support\Collection;
use RainLab\User\Models\User;

/**
 * User Extended by Shawn Clake
 * Class UserManager
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Classes
 */
class UserManager extends StaticFactory
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

        $friends = FriendsManager::getAllFriends();

        foreach($users as $user)
        {

            $userAdd = true;

            if(!$friends->isEmpty())
            {

                foreach($friends as $friend)
                {

                    if($user->id == $friend->id)
                    {
                        $userAdd = false;
                        break;
                    }

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

    /**
     * Used to search for users by phrase. It will search their name, email, surname, and username
     * @param $phrase
     * @deprecated This has been moved to a trait on the UserExtended model
     * @return Collection
     */
    public static function searchUsers($phrase)
    {
        /*$results = Lists::create();

        $results->mergeList(self::searchUserByName($phrase));

        $results->mergeList(self::searchUserByEmail($phrase));

        $results->mergeList(self::searchUserBySurname($phrase));

        $results->mergeList(self::searchUserByUsername($phrase));

        return $results->allList();*/
        return null;

    }

    /**
     * Searches for user models with a name like phrase
     * @param $phrase
     * @deprecated This exists on a trait now
     * @return mixed
     */
    public static function searchUserByName($phrase)
    {
        return User::where('name', 'like', '%' . $phrase . '%')->get();
    }

    /**
     * Searches for user models with an email like phrase
     * @param $phrase
     * @deprecated This exists on a trait now
     * @return mixed
     */
    public static function searchUserByEmail($phrase)
    {
        return User::where('email', 'like', '%' . $phrase . '%')->get();
    }

    /**
     * Searches for user models with a surname like phrase
     * @param $phrase
     * @deprecated This exists on a trait now
     * @return mixed
     */
    public static function searchUserBySurname($phrase)
    {
        return User::where('surname', 'like', '%' . $phrase . '%')->get();
    }

    /**
     * Searches for user models with a username like phrase
     * @param $phrase
     * @deprecated This exists on a trait now
     * @return mixed
     */
    public static function searchUserByUsername($phrase)
    {
        return User::where('username', 'like', '%' . $phrase . '%')->get();
    }

}