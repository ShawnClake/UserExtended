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

    public function getUsers($value, $property = "name")
    {
        return UserUtil::getUsers($value, $property);
    }

    public function getUser($value, $property = "id")
    {
        return UserUtil::getUser($value, $property);
    }

    public function getLoggedInUser($return_type = "ue")
    {
        if($return_type == "ue")
        {
            return UserUtil::getLoggedInUserExtendedUser();
        } else {
            return UserUtil::getLoggedInUser();
        }
    }

    public function getLoggedInUsersTimezone()
    {
        return UserUtil::getLoggedInUsersTimezone();
    }

    public function getUserTimezone($value, $property = "id")
    {
        return UserUtil::getUserTimezone($value, $property);
    }

    public function searchUsers($phrase)
    {
        return UserUtil::searchUsers($phrase);
    }

    public function isLoggedIn($userId)
    {
        return UserUtil::idIsLoggedIn($userId);
    }

}