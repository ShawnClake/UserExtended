<?php

namespace Clake\UserExtended\Classes;

use Auth;
use Carbon\Carbon;
use Clake\Userextended\Models\Timezone;
use Clake\Userextended\Models\UserExtended;
use RainLab\User\Models\User;
use Redirect;

/**
 * Class UserUtil
 * @package Clake\UserExtended\Classes
 *
 * @todo: Move time related methods to a seperate trait
 * @todo: Test casting and timezones
 *
 */
class UserUtil
{

    /**
     * Get all users with the search criteria
     * @param $value
     * @param string $property
     * @return mixed
     */
    public static function getUsers($value, $property = "name")
    {
        return User::where($property, $value)->get();
    }

    /**
     * Get the first user with the search criteria
     * @param $value
     * @param string $property
     * @return mixed
     */
    public static function getUser($value, $property = "id")
    {
        return UserExtended::where($property, $value)->first();
    }

    /**
     * Get the rainlab user instance.
     * Required for backward compatibility with relations like 'avatar'
     * @param $value
     * @param string $property
     * @return mixed
     */
    public static function getRainlabUser($value, $property = "id")
    {
        return User::where($property, $value)->first();
    }

    /**
     * Returns the logged in user. Typically used across all of my plugins
     * @return null
     */
    public static function getLoggedInUser()
    {
        if (!$user = Auth::getUser()) {
            return null;
        }

        $user->touchLastSeen();

        return $user;
    }

    /**
     * Redirect a user if they aren't logged in.
     * DO NOT USE. CURRENTLY BROKEN.
     * @param string $url
     * @return mixed
     */
    public static function redirectIfNotLoggedIn($url = '/')
    {
        if(!self::getLoggedInUser())
            return Redirect::to($url);
    }

    /**
     * Returns a Timezone model for the current logged in user
     * @return mixed|null|string
     */
    public static function getLoggedInUsersTimezone()
    {
        $user = self::getLoggedInUser();

        if($user != null)
        {
            $user = self::castToUserExtendedUser($user);
            return $user->timezone;
        }

        return null;
    }

    /**
     * Get a users current timezone.
     * @param $value
     * @param string $property
     * @return null
     */
    public static function getUserTimezone($value, $property = "id")
    {
        $user = self::getUser($value, $property);
        if($user != null)
        {
            return $user->timezone;
        }
        return null;
    }

    /**
     * Casts the Rainlab.User model to Clake.UserExtended
     * @param UserExtended $user
     * @return User
     */
    public static function castToRainLabUser(UserExtended $user)
    {
        $rainlab = new User();
        $rainlab->attributes = $user->attributes;
        return $rainlab;
    }

    /**
     * Casts the Clake.UserExtended model to Rainlab.User
     * @param User $user
     * @return UserExtended
     */
    public static function castToUserExtendedUser(User $user)
    {
        $userExtended = new UserExtended();
        $userExtended->attributes = $user->attributes;
        return $userExtended;
    }

}