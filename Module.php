<?php namespace Clake\UserExtended;

use Clake\UserExtended\Classes\UserUtil;
use Clake\UserExtended\Traits\StaticFactoryTrait;
use Clake\UserExtended\Classes\UserExtended;

/**
 * User Extended by Shawn Clake
 * Class Module
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended
 */
class Module extends UserExtended
{
    use StaticFactoryTrait;

    public $name = "clakeUserExtended";

    public $author = "Shawn Clake";

    public $description = "The core module for UserExtended";

    public $version = "2.0.00";

    public function initialize() {}

    public function injectComponents()
    {
        return [
            'Clake\UserExtended\Components\Account' => 'account',
            'Clake\UserExtended\Components\Friends' => 'friends',
            'Clake\UserExtended\Components\User'    => 'ueuser',
        ];
    }

    public function injectNavigation()
    {
        return [];
    }

    public function injectLang()
    {
        return [];
    }

    public function injectAssets()
    {
        return [
            //'ueJS'  => '/plugins/clake/userextended/assets/js/general.js',
            //'ueCSS' => '/plugins/clake/userextended/assets/css/general.css'
        ];
    }

    /**
     * Returns users with $property col = $value
     * @param $value
     * @param string $property
     * @return \Rainlab\User\Models\User
     */
    public function getUsers($value, $property = "name")
    {
        return UserUtil::getUsers($value, $property);
    }

    /**
     * Returns a user with $property col = $value
     * @param $value
     * @param string $property
     * @return \Clake\Userextended\Models\UserExtended
     */
    public function getUser($value, $property = "id")
    {
        return UserUtil::getUser($value, $property);
    }

    /**
     * Returns the logged in user. Optional param $return_type is a flag for which type of user object to return
     * @param string $return_type
     * @return \Clake\Userextended\Models\UserExtended|\Rainlab\User\Models\User
     */
    public function getLoggedInUser($return_type = "ue")
    {
        if($return_type == "ue")
        {
            return UserUtil::getLoggedInUserExtendedUser();
        } else {
            return UserUtil::getLoggedInUser();
        }
    }

    /**
     * Returns the timezone code for the logged in user
     * @return string|null
     */
    public function getLoggedInUsersTimezone()
    {
        return UserUtil::getLoggedInUsersTimezone();
    }

    /**
     * Returns the timezone code for a user who's $property col = $value
     * @param $value
     * @param string $property
     * @return null|string
     */
    public function getUserTimezone($value, $property = "id")
    {
        return UserUtil::getUserTimezone($value, $property);
    }

    /**
     * Returns a set of UserExtended models from users found with username, surname, firstname, or email containing $phrase
     * @param $phrase
     * @return \Clake\Userextended\Models\UserExtended
     */
    public function searchUsers($phrase)
    {
        return UserUtil::searchUsers($phrase);
    }

    /**
     * Returns whether or not a user is logged in
     * @param $userId
     * @return bool
     */
    public function isLoggedIn($userId)
    {
        return UserUtil::idIsLoggedIn($userId);
    }

}