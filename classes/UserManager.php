<?php namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Timezone;
use Illuminate\Support\Collection;
use RainLab\User\Models\User;
use Flash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Lang;
use Auth;
use October\Rain\Auth\Manager;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\ValidationException;
use RainLab\User\Models\Settings;
use Mail;
use Event;
use Clake\Userextended\Models\Settings as UserExtendedSettings;
use Cms\Classes\Page;
use Log;

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

        $users = User::all();

		if(empty($users))
		    return $returner;

        $users->random($limit);

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
     * Updates a user
     * @param array $data
     * @param UserExtended|null $user
     * @return bool|Validator\
     */
    public static function updateUser(array $data, UserExtended $user = null)
    {
        if(!isset($user))
        {
            if (!$user = UserUtil::convertToUserExtendedUser(UserUtil::getLoggedInUser())) {
                return false;
            }
        }

        /*
         * Validate input
         */
        $rules = [
            'email'    => 'required|email|between:6,255',
            'password' => UserExtendedSettings::get('validation_password', 'required|between:4,255|confirmed'),
        ];

        /*
         * Better utilization of email vs username
         */
        if (Settings::get('login_attribute') == "username") {
            $rules['username'] = UserExtendedSettings::get('validation_username', 'required|between:4,255');
        }

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            //throw new ValidationException($validation);
            return $validation;
        }

        $settingsValidator = UserSettingsManager::validation();

        foreach($data as $key=>$value)
        {
            if ($key == "_session_key" || $key == "_token" || $key == "name" || $key == "email" || $key == "username" || $key == "password" || $key == "password_confirmation")
                continue;

            $result = $settingsValidator->checkValidation($key, $value);

            /* Valid setting & Validates */
            if($result === true)
                continue;

            /* Not a valid setting */
            if($result === false)
                continue;

            /* Validation Failed */
            if($result->fails())
                return $result;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (strlen($data['password']) && strlen($data['password_confirmation'])) {
            $user->password = $data['password'];
            $user->password_confirmation = $data['password_confirmation'];
        }

        if(isset($data['timezone']))
        {
            $timezone = Timezone::where('abbr', $data['timezone'])->first();
            $user->timezone_id = $timezone->id;
        }

        $user->save();

        $settingsManager = UserSettingsManager::currentUser();

        Event::fire('clake.ue.settings.update', [&$settingsManager]);

        foreach($data as $key=>$value)
        {
            if($key=="_session_key" || $key=="_token" || $key=="name" || $key=="username" || $key=="email" || $key=="password" || $key=="password_confirmation")
                continue;

            if($settingsManager->isSetting($key))
            {
                /** @var $validator bool|Validator\ */
                $validator = $settingsManager->setSetting($key, $value);
                if($validator !== true)
                {
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

        if(isset($data['flash']))
            Flash::success($data['flash']);
        else
            Flash::success(Lang::get('rainlab.user::lang.account.success_saved'));

		Log::info( $data['name'] . " updated their account.");
        return $user;
    }

    /**
     * Programatically registers a user
     * @param array $data
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    public static function registerUser(array $data, array $options = ['default' => true, 'timezone' => true])
    {
        try {
            if (!Settings::get('allow_registration', true)) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.registration_disabled'));
            }

            $eResponse = Event::fire('clake.ue.preregistration', [&$data], true);

            /*
             * Validate input
             */
            $rules = [
                'email'    => 'required|email|between:6,255',
                'password' => UserExtendedSettings::get('validation_password', 'required|between:4,255|confirmed'),
            ];

            /*
             * Better utilization of email vs username
             */
            if (Settings::get('login_attribute') == "username") {
                $rules['username'] = UserExtendedSettings::get('validation_username', 'required|between:4,255');
            }

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                //throw new ValidationException($validation);
                return $validation;
            }

            $settingsValidator = UserSettingsManager::validation();

            foreach($data as $key=>$value)
            {
                if ($key == "_session_key" || $key == "_token" || $key == "name" || $key == "email" || $key == "username" || $key == "password" || $key == "password_confirmation")
                    continue;

                $result = $settingsValidator->checkValidation($key, $value);

                /* Valid setting & Validates */
                if($result === true)
                    continue;

                /* Not a valid setting */
                if($result === false)
                    continue;

                /* Validation Failed */
                if($result->fails())
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
            if(!empty($defaultGroup) && $options['default'])
            {
                UserGroupManager::currentUser()->addGroup($defaultGroup);
            }

            $defaultTimezone = UserExtendedSettings::get('default_timezone', 'UTC');
            if(!empty($defaultGroup) && $options['timezone'])
            {
                $timezone = Timezone::where('abbr', $defaultTimezone)->first();
                $user->timezone_id = $timezone->id;
                $user->save();
            }

            $settingsManager = UserSettingsManager::currentUser();

            Event::fire('clake.ue.settings.create', [&$settingsManager]);

            foreach($data as $key=>$value)
            {
                if($key=="_session_key" || $key=="_token" || $key=="name" || $key=="email" || $key=="username" || $key=="password" || $key=="password_confirmation")
                    continue;

                if($settingsManager->isSetting($key))
                {
                    /** @var $validator bool|Validator\ */
                    $validator = $settingsManager->setSetting($key, $value);
                    if($validator !== true)
                    {
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
			 Log::info( UserUtil::getLoggedInUser()->name . " has created a new account.");
			 
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
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());

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
    public static function sendActivationEmail($user, $link, $code)
    {
        $data = [
            'name' => $user->name,
            'link' => $link,
            'code' => $code
        ];
		
		Log::info( "Confirmation email sent to " . $user->email);
		
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
    protected static function register(array $credentials, $activate = false)
    {
        $user = new \Clake\Userextended\Models\UserExtended();
        $user->name = $credentials['first_name'];
        $user->surname = $credentials['last_name'];

        if(isset($credentials['username']))
            $user->username = $credentials['username'];

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
    public static function loginUser(array $data, $redirect_link = "")
    {
        /*
         * Validate input
         */
        $rules = [];

        $rules['login'] = Settings::get('login_attribute', 'email')  == "username"
            ? 'required|between:2,255'
            : 'required|email|between:6,255';

        $rules['password'] = 'required|between:4,255';

        if (!array_key_exists('login', $data)) {
            if(isset($data['username']))
                $data['login'] = $data['username'];
            else
                $data['login'] = $data['email'];
        }

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            return $validation;
            //throw new ValidationException($validation);
        }

        /*
         * Authenticate user
         */
        $credentials = [
            'login'    => array_get($data, 'login'),
            'password' => array_get($data, 'password')
        ];

        Event::fire('rainlab.user.beforeAuthenticate', [$data, $credentials]);

        $user = Auth::authenticate($credentials, true);

        Event::fire('clake.ue.login', [$user]);

        /*
         * Redirect to the intended page after successful sign in
         */
		 Log::info($data['login'] . " successfully logged in.");
		 
        $redirectUrl = $redirect_link;

        if ($redirectUrl = input('redirect', $redirectUrl)) {
            return Redirect::intended($redirectUrl);
        }

    }

    /**
     * Logs out the currently logged in user
     * @return mixed
     */
    public static function logoutUser()
    {
        $user = Auth::getUser();

        if (!isset($user)) {
            return false;
        }

		Log::info(UserUtil::getLoggedInUser()->name . " successfully logged out.");
        Auth::logout();
        Event::fire('rainlab.user.logout', [$user]);
        Event::fire('clake.ue.logout', [$user]);
        Flash::success(Lang::get('rainlab.user::lang.session.logout'));

        $url = post('redirect', Request::fullUrl());
        return Redirect::to($url);
    }

    public static function loginUserObj($user)
    {
        Auth::login($user);
    }

}