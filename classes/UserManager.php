<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Timezone;
use Illuminate\Support\Collection;
use RainLab\User\Models\User;
use Flash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Lang;
use Auth;
use October\Rain\Exception\ApplicationException;
use RainLab\User\Models\Settings;
use Mail;
use Event;
use Clake\Userextended\Models\Settings as UserExtendedSettings;
use Log;

/**
 * User Extended by Shawn Clake
 * Class UserManager
 * User Extended is licensed under the MIT license.
 *
 * TODO: This class needs a complete refactor as its becoming a mess
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Classes
 */
class UserManager extends StaticFactory {

    /**
     * Returns a random set of users. This won't return users in our friends list.
     * @param int $limit
     * @return Collection
     */
    public static function getRandomUserSet($limit = 5) {
        $returner = new Collection;

        $userCount = User::all()->count();

        if (!isset($userCount) || empty($userCount) || $userCount == 0)
            return [];

        if ($userCount < $limit)
            $limit = $userCount;

        $users = User::all();

        if (empty($users))
            return $returner;

        $users->random($limit);

        $friends = FriendsManager::getAllFriends();

        foreach ($users as $user) {
            $userAdd = true;

            if (!$friends->isEmpty()) {
                foreach ($friends as $friend) {
                    if ($user->id == $friend->id) {
                        $userAdd = false;
                        break;
                    }
                }
            }

            if ($user->id == UserUtil::getLoggedInUser()->id)
                $userAdd = false;

            if ($userAdd) {
                $returner->push($user);
            }
        }

        return $returner;
    }

    /**
     * Updates a user
     * @param array $data
     * @param UserExtended|null $user
     * @return bool|Validator\
     */
    public static function updateUser(array $data, \Clake\Userextended\Models\UserExtended $user = null) {

        if (!isset($user)) {
            if (!$user = UserUtil::convertToUserExtendedUser(UserUtil::getLoggedInUser())) {
                Log::info("Error updating user.");
                Log::info(UserUtil::getLoggedInUser());
                return false;
            }
        }

        /*
         * Validate input
         */
        $rules = [
            'email' => 'required|email|between:6,255',
        ];

        /*
         * Better utilization of email vs username
         */
        if (Settings::get('login_attribute') == "username") {
            $rules['username'] = 'required|between:2,255';
        }

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            //throw new ValidationException($validation);
            return $validation;
        }

        $settingsValidator = UserSettingsManager::validation();

        foreach ($data as $key => $value) {
            if ($key == "_session_key" || $key == "_token" || $key == "name" || $key == "email" || $key == "username")
                continue;

            $result = $settingsValidator->checkValidation($key, $value);

            /* Valid setting & Validates */
            if ($result === true)
                continue;

            /* Not a valid setting */
            if ($result === false)
                continue;

            /* Validation Failed */
            if ($result->fails())
                return $result;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];


        if (isset($data['timezone']) && $data['timezone'] != 0) {
            //$timezone = Timezone::where('abbr', $data['timezone'])->first();
            //echo $data['timezone'];
            $user->timezone_id = $data['timezone'];
        }

        $user->save();

        $settingsManager = UserSettingsManager::currentUser();

        Event::fire('clake.ue.settings.update', [&$settingsManager]);

        foreach ($data as $key => $value) {
            if ($key == "_session_key" || $key == "_token" || $key == "name" || $key == "username" || $key == "email")
                continue;

            if ($settingsManager->isSetting($key)) {
                /** @var $validator bool|Validator\ */
                $validator = $settingsManager->setSetting($key, $value);
                if ($validator !== true) {
                    /*
                     * This means validation failed and the setting was NOT set.
                     * $validator is a Validator instance
                     */
                    return $validator;
                }
            }
        }

        $settingsManager->save();

        if (isset($data['flash']))
            Flash::success($data['flash']);
        else
            Flash::success(Lang::get('rainlab.user::lang.account.success_saved'));

        Log::info($data['name'] . " updated their account.");
        Log::info($data);

        return $user;
    }

        /**
     * Updates a user
     * @param array $data
     * @param UserExtended|null $user
     * @return bool|Validator\
     */
    public static function updateUserPassword(array $data, \Clake\Userextended\Models\UserExtended $user = null) {

        if (!isset($user)) {
            if (!$user = UserUtil::convertToUserExtendedUser(UserUtil::getLoggedInUser())) {
                Log::info("Error updating user.");
                Log::info(UserUtil::getLoggedInUser());
                return false;
            }
        }

        /*
         * Validate input
         */
        $rules = [
            'password' => UserExtendedSettings::get('validation_password', 'between:4,255|confirmed'),
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            //throw new ValidationException($validation);
            return $validation;
        }

        $settingsValidator = UserSettingsManager::validation();

        foreach ($data as $key => $value) {
            if ($key == "_session_key" || $key == "_token" || $key == "password" || $key == "password_confirmation")
                continue;

            $result = $settingsValidator->checkValidation($key, $value);

            /* Valid setting & Validates */
            if ($result === true)
                continue;

            /* Not a valid setting */
            if ($result === false)
                continue;

            /* Validation Failed */
            if ($result->fails())
                return $result;
        }

        if (strlen($data['password']) && strlen($data['password_confirmation'])) {
            $user->password = $data['password'];
            $user->password_confirmation = $data['password_confirmation'];
        }

        $user->save();

        $settingsManager = UserSettingsManager::currentUser();

        Event::fire('clake.ue.settings.update', [&$settingsManager]);

        foreach ($data as $key => $value) {
            if ($key == "_session_key" || $key == "_token" || $key == "password" || $key == "password_confirmation")
                continue;

            if ($settingsManager->isSetting($key)) {
                /** @var $validator bool|Validator\ */
                $validator = $settingsManager->setSetting($key, $value);
                if ($validator !== true) {
                    /*
                     * This means validation failed and the setting was NOT set.
                     * $validator is a Validator instance
                     */
                    return $validator;
                }
            }
        }

        $settingsManager->save();

        if (strlen($data['password'])) {
            Auth::login($user->reload(), true);
        }

        if (isset($data['flash']))
            Flash::success($data['flash']);
        else
            Flash::success(Lang::get('rainlab.user::lang.account.success_saved'));

        Log::info($user->name . " updated their account.");
        Log::info($data);

        return $user;
    }
    
    /**
     * Programatically registers a user
     * @param array $data
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public static function registerUser(array $data, array $options = ['default' => true, 'timezone' => true]) {
        try {
            if (!Settings::get('allow_registration', true)) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.registration_disabled'));
            }

            $eResponse = Event::fire('clake.ue.preregistration', [&$data], true);

            /*
             * Validate input
             */
            $rules = [
                'email' => 'required|email|between:6,255|unique:users,email',
                'password' => UserExtendedSettings::get('validation_password', 'required|between:4,255|confirmed'),
            ];

            /*
             * Better utilization of email vs username
             */
            if (Settings::get('login_attribute') == "username") {
                $rules['username'] = UserExtendedSettings::get('validation_username', 'required|between:4,255|unique:users,username');
            }

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                //throw new ValidationException($validation);
                return $validation;
            }

            $settingsValidator = UserSettingsManager::validation();

            foreach ($data as $key => $value) {
                if ($key == "_session_key" || $key == "_token" || $key == "name" || $key == "email" || $key == "username" || $key == "password" || $key == "password_confirmation")
                    continue;

                $result = $settingsValidator->checkValidation($key, $value);

                /* Valid setting & Validates */
                if ($result === true)
                    continue;

                /* Not a valid setting */
                if ($result === false)
                    continue;

                /* Validation Failed */
                if ($result->fails())
                    return $result;
            }

            /*
             * Register user
             */
            $requireActivation = Settings::get('require_activation', true);
            $automaticActivation = Settings::get('activate_mode') == Settings::ACTIVATE_AUTO;
            $userActivation = Settings::get('activate_mode') == Settings::ACTIVATE_USER;

            /*
             * Preform phase 1 User registration
             */
            $user = self::register($data, $automaticActivation);

            Auth::login($user);

            /*
             * Preform phase 2 User registration
             */
            $defaultGroup = UserExtendedSettings::get('default_group', '');
            if (!empty($defaultGroup) && $options['default']) {
                UserGroupManager::currentUser()->addGroup($defaultGroup);
            }

            $defaultTimezone = UserExtendedSettings::get('default_timezone', 'UTC');
            if (!empty($defaultTimezone) && $options['timezone']) {
                $user->timezone_id = $defaultTimezone;
                $user->save();
            }

            $settingsManager = UserSettingsManager::currentUser();

            Event::fire('clake.ue.settings.create', [&$settingsManager]);

            foreach ($data as $key => $value) {
                if ($key == "_session_key" || $key == "_token" || $key == "name" || $key == "email" || $key == "username" || $key == "password" || $key == "password_confirmation")
                    continue;

                if ($settingsManager->isSetting($key)) {
                    /** @var $validator bool|Validator\ */
                    $validator = $settingsManager->setSetting($key, $value);
                    if ($validator !== true) {
                        return $validator;
                        /*
                         * This means validation failed and the setting was NOT set.
                         * $validator is a Validator instance
                         */
                    }
                }
            }

            $settingsManager->save();

            /*
             * Preform phase 3 User registration
             * Modified to swap to logout
             * Automatically activated or not required, log the user in
             */
            Log::info(UserUtil::getLoggedInUser()->name . " has created a new account.");
            Log::info(UserUtil::getLoggedInUser());


            if (!$automaticActivation || $requireActivation) {
                $user = UserUtil::convertToUserExtendedUser(UserUtil::getLoggedInUser());
                $user->last_login = null;
                $user->last_seen = null;
                Event::fire('clake.ue.postregistration', [&$user]);
                $user->save();
                Auth::logout();
            }

            return $user;
        } catch (\Exception $ex) {
            if (Request::ajax())
                throw $ex;
            else
                Flash::error($ex->getMessage());

            return $validation;
        }
    }

    /**
     * Sends the activation email to a user
     * Copied from the RainLab.Users Account component
     * Altered by Shawn Clake
     * @param  User $user
     * @return void
     */
    public static function sendActivationEmail($user, $link, $code) {
        $data = [
            'name' => $user->name,
            'link' => $link,
            'code' => $code
        ];

        Mail::send('rainlab.user::mail.activate', $data, function($message) use ($user) {
            $message->to($user->email, $user->name);
        });
    }

    /**
     * Registers the user
     * Copied from the RainLab.Users Account component
     * Altered by Shawn Clake
     * @param array $credentials
     * @param bool $activate
     * @return mixed
     */
    protected static function register(array $credentials, $activate = false) {
        $user = new \Clake\Userextended\Models\UserExtended();
//        $user->name = $credentials['first_name'];
//        $user->surname = $credentials['last_name'];

        if (isset($credentials['username']))
            $user->username = $credentials['username'];

        if (isset($credentials['username']))
            $user->name = ucfirst($credentials['username']);

        $user->email = $credentials['email'];
        $user->password = $credentials['password'];
        $user->password_confirmation = $credentials['password_confirmation'];
        $user->save();

        if ($activate) {
            $user->attemptActivation($user->getActivationCode());
        }

        // Prevents revalidation of the password field
        // on subsequent saves to this model object
        $user->password = null;

        return $user;
    }

    /**
     * Logs in a user
     * @param array $data
     * @param string $redirect_link
     * @return mixed
     */
    public static function loginUser(array $data, $redirect_link = "") {

        /*
         * Validate input
         */
        $rules = [];

        $rules['login'] = Settings::get('login_attribute', 'email') == "username" ? 'required|between:2,255' : 'required|email|between:6,255';

        $rules['password'] = 'required|between:4,255';

        if (!array_key_exists('login', $data)) {
            if (isset($data['username']))
                $data['login'] = $data['username'];
            else
                $data['login'] = $data['email'];
        }

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            return $validation;
            //throw new ValidationException($validation);
        }

        self::checkForReopenAccount($data);

        /*
         * Authenticate user
         */
        $credentials = [
            'login' => array_get($data, 'login'),
            'password' => array_get($data, 'password')
        ];

        Event::fire('rainlab.user.beforeAuthenticate', [$data, $credentials]);

        $user = Auth::authenticate($credentials, true);

        self::checkForSuspendedAccount($user);

        Event::fire('clake.ue.login', [$user]);
        //self::suspendAccount($user);
        /*
         * Redirect to the intended page after successful sign in
         */
        $redirectUrl = $redirect_link;

        if ($redirectUrl = input('redirect', $redirectUrl)) {
            return Redirect::intended($redirectUrl);
        }
    }

    /**
     * Logs out the currently logged in user
     * @return mixed
     */
    public static function logoutUser() {
        $user = Auth::getUser();

        if (!isset($user)) {
            return false;
        }

        Log::info(UserUtil::getLoggedInUser()->name . " successfully logged out.");
        Log::info(UserUtil::getLoggedInUser());
        Auth::logout();
        Event::fire('rainlab.user.logout', [$user]);
        Event::fire('clake.ue.logout', [$user]);
        Flash::success(Lang::get('rainlab.user::lang.session.logout'));

        $url = post('redirect', Request::fullUrl());

        return Redirect::to($url);
    }

    /**
     * Used by 3rd party integrations to login
     * @param $user
     */
    public static function loginUserObj($user) {
        self::checkForReopenAccount($user);
        Auth::login($user);
        self::checkForSuspendedAccount($user);
    }

    /**
     * Closes the logged in users account
     */
    public static function closeAccount() {
        $user = UserUtil::getLoggedInUserExtendedUser();

        $delete = UserExtendedSettings::get('closing_deletes', 'false');

        Auth::logout();

        Helpers::deleteModel($user, $delete);
    }

    /**
     * Checks for a closed account and reopens it if there is one
     * @param $data
     */
    public static function checkForReopenAccount($data) {
        $ueTrashed = \Clake\Userextended\Models\UserExtended::onlyTrashed();
        $ue = \Clake\Userextended\Models\UserExtended::withTrashed();
        //echo 'hi';
        //echo(Settings::get('login_attribute', 'email'));
        if (Settings::get('login_attribute', 'email') == "email") {
            $ueTrashed->where('email', $data['email']);
            $ue->where('email', $data['email']);
        } else {
            $ueTrashed->where('username', $data['username']);
            $ue->where('username', $data['username']);
        }

        $ueTrashedCount = $ueTrashed->count();
        $ueCount = $ue->count();

        //echo($ueTrashedCount);

        if ($ueTrashedCount == 1 && $ueCount == 0)
            $ueTrashed->first()->restore();
    }

    /**
     * Checks whether an account is suspended and if it is, logs the user back out again
     * @param $user
     * @return bool
     */
    public static function checkForSuspendedAccount($user) {
        if (UserSettingsManager::with(UserUtil::convertToUserExtendedUser($user))->getSetting('core-suspended')[0]) {
            Auth::logout();
            return false;
        }
    }

    /**
     * Deletes an account. Data is NOT recoverable.
     * @param $user
     */
    public static function deleteAccount($user) {
        Helpers::deleteModel($user, true);
    }

    /**
     * Suspends a users account. Denies them from logging in
     * @param $user
     */
    public static function suspendAccount($user) {
        $settings = UserSettingsManager::with(UserUtil::convertToUserExtendedUser($user));
        $settings->setSetting('core-suspended', true);
        $settings->save();
    }

    /**
     * Unsuspends a users account. Allows them to login again
     * @param $user
     */
    public static function unSuspendAccount($user) {
        $settings = UserSettingsManager::with(UserUtil::convertToUserExtendedUser($user));
        $settings->setSetting('core-suspended', false);
        $settings->save();
    }

}
