<?php namespace Clake\UserExtended;

use Clake\UserExtended\Classes\Helpers;
use Clake\UserExtended\Classes\UserGroupManager;
use Clake\UserExtended\Classes\UserManager;
use Clake\UserExtended\Classes\UserRoleManager;
use Clake\UserExtended\Classes\UserSettingsManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\Friend;
use Clake\UserExtended\Traits\StaticFactoryTrait;
use Clake\UserExtended\Classes\UserExtended;
use Backend;

/**
 * User Extended Core by Shawn Clake
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

    public $description = "User Extended Core contains all of the components, navigation, assets, utility functions, and documentation which User Extended depends on.";

    public $version = "2.3.00";

    public function initialize() {}

    public function injectComponents()
    {
        return [
            'Clake\UserExtended\Components\Account'    => 'account',
            'Clake\UserExtended\Components\Friends'    => 'friends',
            'Clake\UserExtended\Components\User'       => 'ueuser',
            //'Clake\UserExtended\Components\ThirdParty' => 'thirdparty',
            'Clake\UserExtended\Components\Routes'     => 'routes',
        ];
    }

    public function injectNavigation()
    {
        return [
            'users-side' => [
                'label' => 'Users',
                'url'   => Backend::url('rainlab/user/users'),
                'icon'  => 'icon-user',
                'order' => 100
            ],
            'routes' => [
                'label' => 'Routes',
                'url'   => Backend::url('clake/userextended/routes/index'),
                'icon'  => 'icon-eye-slash',
                'order' => 200
            ],
            'fields' => [
                'label' => 'Field Manager',
                'url'   => Backend::url('clake/userextended/fields/manage'),
                'icon'  => 'icon-pencil-square-o',
                'order' => 300
            ],
            'roles' => [
                'label' => 'Role Manager',
                'url'   => Backend::url('clake/userextended/roles/manage'),
                'icon'  => 'icon-pencil',
                'order' => 400
            ],
            'friends' => [
                'label' => 'Friends',
                'url'   => Backend::url('clake/userextended/friends/index'),
                'icon'  => 'icon-users',
                'order' => 500
            ],
            'timezones' => [
                'label' => 'Timezones',
                'url'   => Backend::url('clake/userextended/timezones/index'),
                'icon'  => 'icon-clock-o',
                'order' => 600
            ],
            'comments' => [
                'label' => 'Comments',
                'url'   => Backend::url('clake/userextended/comments/index'),
                'icon'  => 'icon-comments-o',
                'order' => 700
            ],
            'modules' => [
                'label' => 'Modules',
                'url'   => Backend::url('clake/userextended/modules/index'),
                'icon'  => 'icon-puzzle-piece',
                'order' => 800
            ],
        ];
    }

    public function injectLang()
    {
        return [];
    }

    public function injectAssets()
    {
        return [
            'ueJS'  => '/plugins/clake/userextended/assets/js/frontend.js',
            'ueCSS' => '/plugins/clake/userextended/assets/css/frontend.css'
        ];
    }

    public function injectBonds()
    {
        return [];
    }

    /**
     * Returns the plugin version notes for display in the Module Manager
     * @return array
     */
    public function getUpdateNotes()
    {
        return [
            '2.2.00' => [Helpers::file(plugins_path('clake/userextended/help/Updates/2.2.00.md')), 'md' => true],
        ];
    }

    /**
     * Returns the plugin documentation for display in the Module Manager
     * @return array
     */
    public function getDocumentation()
    {
        $home = Helpers::file(plugins_path('clake/userextended/readme.md'));

        $apis = Helpers::file(plugins_path('clake/userextended/help/APIs/Helpers.md')) .
            Helpers::file(plugins_path('clake/userextended/help/APIs/Module.md')) .
            Helpers::file(plugins_path('clake/userextended/help/APIs/UserUtil.md'));

        $backend = Helpers::file(plugins_path('clake/userextended/help/Backend/FieldManager.md')) .
            Helpers::file(plugins_path('clake/userextended/help/Backend/RoleManager.md')) .
            Helpers::file(plugins_path('clake/userextended/help/Backend/Routes.md')) .
            Helpers::file(plugins_path('clake/userextended/help/Backend/Timezones.md'));

        $components = Helpers::file(plugins_path('clake/userextended/help/Components/3rdParty.md')) .
            Helpers::file(plugins_path('clake/userextended/help/Components/Account.md')) .
            Helpers::file(plugins_path('clake/userextended/help/Components/Friends.md')) .
            Helpers::file(plugins_path('clake/userextended/help/Components/User.md'));

        $traits = Helpers::file(plugins_path('clake/userextended/help/Traits/Searchable.md')) .
            Helpers::file(plugins_path('clake/userextended/help/Traits/Timezonable.md'));

        return [
            'home'       => [$home, 'md' => true],
            'apis'       => [$apis, 'md' => true],
            'backend'    => [$backend, 'md' => true],
            'components' => [$components, 'md' => true],
            'traits'     => [$traits, 'md' => true],
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

    /**
     * Programmatically registers a user.
     * The $data array requires the following indices: email, password, password_confirmation
     * The $data array can optional have the following indices: username, name
     * The $data array also supports dynamic user settings. The index names are the same as the setting code found in user_settings.yaml
     * @param array $data
     * @return bool|mixed
     */
    public function registerUser(array $data)
    {
        return UserManager::registerUser($data);
    }

    /**
     * Programmatically update a user. Defaults to logged in user if a UserExtended object isn't passed in.
     * The $data array can contain the following indices: email, password, password_confirmation, username, name
     * The $data array also supports dynamic user settings. The index names are the same as the setting code found in user_settings.yaml
     * @param array $data
     * @param UserExtended|null $user
     * @return bool|\Illuminate\Support\Facades\Validator\
     */
    public function updateUser(array $data, UserExtended $user = null)
    {
        return UserManager::updateUser($data, $user);
    }

    /**
     * Programmatically logs in a user
     * The $data array requires the following indices: password, email or username (Depends on the default login field)
     * @param array $data
     * @return mixed
     */
    public function loginUser(array $data)
    {
        return UserManager::loginUser($data);
    }

    /**
     * Programmatically logs out the currently logged in user
     * @return mixed
     */
    public function logoutUser()
    {
        return UserManager::logoutUser();
    }

    /**
     * Returns an array of UserSettings
     * @return array
     */
    public function getUserSettings()
    {
        return UserSettingsManager::init()->all();
    }

    /**
     * Programmatically adds a user to a group
     * @param $groupCode
     * @param null $user
     * @return bool
     */
    public function addUserToGroup($groupCode, $user = null)
    {
        return UserGroupManager::with($user)->addGroup($groupCode);
    }

    /**
     * Programmatically removes a user from a group
     * @param $groupCode
     * @param null $user
     * @return bool
     */
    public function removeUserFromGroup($groupCode, $user = null)
    {
        return UserGroupManager::with($user)->removeGroup($groupCode);
    }

    /**
     * Programmatically adds a user to a role
     * @param $roleCode
     * @param null $user
     * @return bool
     */
    public function addUserToRole($roleCode, $user = null)
    {
        return UserRoleManager::with($user)->addRole($roleCode);
    }

    /**
     * Programmatically removes a user from a role.
     * @param $roleCode
     * @param null $user
     * @return bool
     */
    public function removeUserFromRole($roleCode, $user = null)
    {
        return UserRoleManager::with($user)->removeRole($roleCode);
    }

    /**
     * Checks whether a bond state exists between the logged in user and the user specified by $userId
     * @param $bond
     * @param $userId
     * @return bool
     */
    public function bondExists($bond, $userId)
    {
        return Friend::isBond($bond, $userId);
    }


}