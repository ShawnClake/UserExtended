<?php

namespace Clake\Userextended\Components;

use Clake\UserExtended\Classes\UserManager;
use Clake\Userextended\Models\Timezone;
use Clake\UserExtended\Plugin;
use Cms\Classes\ComponentBase;
use Clake\UserExtended\Classes\UserSettingsManager;
use Clake\UserExtended\Classes\UserUtil;
use Flash;
use Illuminate\Support\Facades\Redirect;
use Lang;
use Auth;
use October\Rain\Exception\ValidationException;
use RainLab\User\Models\Settings;
use Mail;
use Event;
use Clake\Userextended\Models\Settings as UserExtendedSettings;
use Cms\Classes\Page;
use Illuminate\Support\Facades\Input;

/**
 * User Extended by Shawn Clake
 * Class Account
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Components
 *
 * TODO: Refactor this class to reduce some function bulk
 *
 * Some code in this component has been copied from the RainLab.User plugin.
 * Find the original plugin here: https://github.com/rainlab/user-plugin
 * Copied and modified functions:
 *  * onUpdate
 *  * onRegister
 *  * sendActivationEmail
 *  * register
 *  * onLogin
 *  * onLogout
 */
class Account extends ComponentBase {

    public function componentDetails() {
        return [
            'name' => 'Account',
            'description' => 'Register, login, logout, settings, update'
        ];
    }

    public function defineProperties() {
        return [
            'type' => [
                'title' => 'Type',
                'type' => 'dropdown',
                'default' => 'random',
                'placeholder' => 'Select type',
            ],
            'redirect' => [
                'title' => 'rainlab.user::lang.account.redirect_to',
                'description' => 'rainlab.user::lang.account.redirect_to_desc',
                'type' => 'dropdown',
                'default' => ''
            ],
            'paramCode' => [
                'title' => 'rainlab.user::lang.account.code_param',
                'description' => 'rainlab.user::lang.account.code_param_desc',
                'type' => 'string',
                'default' => 'code'
            ]
        ];
    }

    /**
     * Used for properties dropdown menu
     * @return mixed
     */
    public function getRedirectOptions() {
        return ['' => '- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Injects assets
     */
    public function onRun() {
        Plugin::injectAssets($this);
    }

    /**
     * Copied from the RainLab.Users Account component
     * Altered by Shawn Clake
     */
    public function onUpdate() {
        $data = post();
        //echo json_encode($data);
        $response = UserManager::updateUser($data);

        $reflection = new \ReflectionClass($response);

        if ($reflection->getShortName() == 'Validator') {
            throw new ValidationException($response);
        } else {
            return $response;
        }
    }

    /**
     * Copied from the RainLab.Users Account component
     * Altered by Shawn Clake
     */
    public function onUpdatePassword() {
        $data = post();
        //echo json_encode($data);
        $response = UserManager::updateUserPassword($data);

        $reflection = new \ReflectionClass($response);

        if ($reflection->getShortName() == 'Validator') {
            throw new ValidationException($response);
        } else {
            return $response;
        }
    }

    /**
     * AJAX handler for registering a user
     * Copied from the RainLab.Users Account component
     * Altered by Shawn Clake
     * @return mixed
     * @throws \Exception
     */
    public function onRegister() {
        $data = post();

        /*
         * Preforms user registration
         */
        if (!($user = UserManager::registerUser($data)))
            return false;

        /*
         * Checks for passed validation or failed
         */
        $reflection = new \ReflectionClass($user);

        if ($reflection->getShortName() == 'Validator') {
            throw new ValidationException($user);
            //Flash::error($user->messages());
            //return false;
        }

        /*
         * Sends an activation email if required
         */
        $userActivation = Settings::get('activate_mode') == Settings::ACTIVATE_USER;

        if ($userActivation) {
            $code = implode('!', [$user->id, $user->getActivationCode()]);
            $link = $this->currentPageUrl([
                $this->property('paramCode') => $code
            ]);

            UserManager::sendActivationEmail($user, $link, $code);

            Flash::success(Lang::get('rainlab.user::lang.account.activation_email_sent'));
        }

        /*
         * Redirect to the intended page after successful sign in
         */
        $redirectUrl = $this->pageUrl($this->property('redirect')) ?: $this->property('redirect');

        if ($redirectUrl = post('redirect', $redirectUrl)) {
            return Redirect::intended($redirectUrl);
        }
    }

    /**
     * Logs in a user
     * Copied from the RainLab.Users Account component
     * Altered by Shawn Clake
     * @return mixed
     * @throws ValidationException
     */
    public function onLogin() {
        $data = post();
        $redirectUrl = $this->pageUrl($this->property('redirect')) ?: $this->property('redirect');

        $response = UserManager::loginUser($data, $redirectUrl);

        if ($response === false) {
            Flash::error('Your account has been suspended.');
            return false;
        }

        $reflection = new \ReflectionClass($response);

        if ($reflection->getShortName() == 'Validator') {
            throw new ValidationException($response);
        } else {
            Flash::success('Logged in!');
            return $response;
        }
    }

    /**
     * Logs out a user
     * Copied from the RainLab.Users Session component
     * Altered by Shawn Clake
     * @return mixed
     */
    public function onLogout() {
        return UserManager::logoutUser();
    }

    /**
     * Returns whether or not we are logging in using email or username
     * @return mixed
     */
    public function signUp() {
        return Settings::get('login_attribute', 'email');
    }

    /**
     * Returns the logged in UserExtended object
     * @return mixed
     */
    public function user() {
        return UserUtil::convertToUserExtendedUser(UserUtil::getLoggedInUser());
    }

    /**
     * Returns an object of user settings
     * @return array
     */
    public function updateSettings() {
        return UserSettingsManager::currentUser()->getUpdateable();
    }

    /**
     * Returns an object of user settings
     * @return array
     */
    public function createSettings() {
        return UserSettingsManager::currentUser()->getRegisterable();
    }

    /**
     * Returns a list of timezones
     * Useful for dropdown menus
     * TODO: This should be moved to timezone helper
     * @return array
     */
    public function timezoneOptions() {
        return Timezone::getTimezonesList();
    }

    /**
     * Returns the current users timezone.
     * TODO: A new function should be created which returns the current users timezone abbr instead of the model
     * @return mixed
     */
    public function myTimezone() {
        return UserUtil::getLoggedInUsersTimezone()->id;
    }

    /**
     * Returns whether or not timezones are enabled.
     * TODO: These checks should be placed in the TimezoneManager
     * @return mixed
     */
    public function timezonesEnabled() {
        return UserExtendedSettings::get('enable_timezones', true);
    }

    /**
     * Closes the logged in users account
     */
    public function onCloseAccount() {
        UserManager::closeAccount();
    }

    /**
     * Returns the user's avatar model relation
     * @return mixed
     */
    public function userAvatar() {
        return UserUtil::getRainlabUser($this->user()->id)->avatar;
    }

    /**
     * AJAX handler for updating a users avatar
     */
    public function onChangeAvatar() {
        if (Input::hasFile('avatar')) {
            $user = UserUtil::getRainlabUser($this->user()->id);
            $user->avatar = Input::file('avatar');
            $user->save();
        }
    }

}
