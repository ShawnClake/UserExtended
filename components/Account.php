<?php namespace Clake\Userextended\Components;

use Clake\Userextended\Models\UserExtended;
use Cms\Classes\ComponentBase;
use Clake\UserExtended\Classes\UserSettingsManager;
use Clake\UserExtended\Classes\UserUtil;
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
//use RainLab\User\Classes\AuthManager

/**
 * Class Account
 * @package Clake\Userextended\Components
 *
 * Some code in this component has been copied from the RainLab.User plugin.
 * Find their original plugin here: https://github.com/rainlab/user-plugin
 * Copied and modified functions:
 *  * onUpdate
 *  * onRegister
 *  * sendActivationEmail
 *  * register
 *  * onLogin
 *  * onLogout
 */
class Account extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Account',
            'description' => 'Register, login, logout, settings, update'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'title'       => 'rainlab.user::lang.account.redirect_to',
                'description' => 'rainlab.user::lang.account.redirect_to_desc',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'paramCode' => [
                'title'       => 'rainlab.user::lang.account.code_param',
                'description' => 'rainlab.user::lang.account.code_param_desc',
                'type'        => 'string',
                'default'     => 'code'
            ]
        ];
    }

    /**
     * Copied from the RainLab.Users Account component
     * Altered by Shawn Clake
     */
    public function onUpdate()
    {
        if (!$user = $this->user()) {
            return;
        }

        $values = post();

        $user->name = $values['name'];
        $user->email = $values['email'];

        $user->save();

        $settingsManager = UserSettingsManager::init();

        foreach($values as $key=>$value)
        {
            if($key=="_session_key" || $key=="_token" || $key=="name" || $key=="username" || $key=="email" || $key=="password" || $key=="password_confirmation")
                continue;

            if($settingsManager->isSetting($key))
                $settingsManager->setSetting($key, $value);
        }

        $settingsManager->save();

        if (strlen(post('password'))) {
            Auth::login($user->reload(), true);
        }

        Flash::success(post('flash', Lang::get('rainlab.user::lang.account.success_saved')));

    }

    /**
     * AJAX handler for registering a user
     * Copied from the RainLab.Users Account component
     * Altered by Shawn Clake
     * @return mixed
     * @throws \Exception
     */
    public function onRegister()
    {

        try {
            if (!Settings::get('allow_registration', true)) {
                throw new ApplicationException(Lang::get('rainlab.user::lang.account.registration_disabled'));
            }

            /*
             * Validate input
             */
            $data = post();

            $rules = [
                'email'    => 'required|email|between:6,255',
                'password' => 'required|between:4,255'
            ];

            /*
             * Better utilization of email vs username
             */
            if (Settings::get('login_attribute') == "username") {
                $rules['username'] = 'required|between:2,255';
            }

            /*
             * Enforcing password confirmation instead of overriding ove rit
             */

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Register user
             */
            $requireActivation = Settings::get('require_activation', true);
            $automaticActivation = Settings::get('activate_mode') == Settings::ACTIVATE_AUTO;
            $userActivation = Settings::get('activate_mode') == Settings::ACTIVATE_USER;
            $user = $this->register($data, $automaticActivation);

            /*
             * Activation is by the user, send the email
             */
            if ($userActivation) {
                $this->sendActivationEmail($user);

                Flash::success(Lang::get('rainlab.user::lang.account.activation_email_sent'));
            }

            /*
             * Modified code below
             */

            Auth::login($user);

            $settingsManager = UserSettingsManager::init();

            foreach($data as $key=>$value)
            {
                if($key=="_session_key" || $key=="_token" || $key=="name" || $key=="email" || $key=="username" || $key=="password" || $key=="password_confirmation")
                    continue;

                if($settingsManager->isSetting($key))
                    $settingsManager->setSetting($key, $value);
            }

            $settingsManager->save();

            /*
             * Modified to swap to logout
             * Automatically activated or not required, log the user in
             */
            if (!$automaticActivation || $requireActivation) {
                $user = UserUtil::getLoggedInUser();
                $user->last_login = null;
                $user->last_seen = null;
                $user->save();
                Auth::logout();
            }

            /*
             * Redirect to the intended page after successful sign in
             */
            $redirectUrl = $this->pageUrl($this->property('redirect'))
                ?: $this->property('redirect');

            if ($redirectUrl = post('redirect', $redirectUrl)) {
                return Redirect::intended($redirectUrl);
            }

        }
        catch (\Exception $ex) {
            if (Request::ajax()) throw $ex;
            else Flash::error($ex->getMessage());
        }

    }

    /**
     * Sends the activation email to a user
     * Copied from the RainLab.Users Account component
     * Altered by Shawn Clake
     * @param  User $user
     * @return void
     */
    protected function sendActivationEmail($user)
    {
        $code = implode('!', [$user->id, $user->getActivationCode()]);
        $link = $this->currentPageUrl([
            $this->property('paramCode') => $code
        ]);

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
    public function register(array $credentials, $activate = false)
    {
        $user = new UserExtended();
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

        return $this->user = $user;
    }

    /**
     * Logs in a user
     * Copied from the RainLab.Users Account component
     * Altered by Shawn Clake
     * @return mixed
     * @throws ValidationException
     */
    public function onLogin()
    {
        /*
         * Validate input
         */
        $data = post();
        $rules = [];

        $rules['login'] = $this->signUp()  == "username"
            ? 'required|between:2,255'
            : 'required|email|between:6,255';

        $rules['password'] = 'required|between:4,255';

        if (!array_key_exists('login', $data)) {
            $data['login'] = post('username', post('email'));
        }

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        /*
         * Authenticate user
         */
        $credentials = [
            'login'    => array_get($data, 'login'),
            'password' => array_get($data, 'password')
        ];

        Event::fire('rainlab.user.beforeAuthenticate', [$this, $credentials]);

        $user = Auth::authenticate($credentials, true);

        /*
         * Redirect to the intended page after successful sign in
         */
        $redirectUrl = $this->pageUrl($this->property('redirect'))
            ?: $this->property('redirect');

        if ($redirectUrl = input('redirect', $redirectUrl)) {
            return Redirect::intended($redirectUrl);
        }
    }

    /**
     * Logs out a user
     * Copied from the RainLab.Users Session component
     * Altered by Shawn Clake
     * @return mixed
     */
    public function onLogout()
    {
        $user = Auth::getUser();

        Auth::logout();

        if ($user) {
            Event::fire('rainlab.user.logout', [$user]);
        }

        $url = post('redirect', Request::fullUrl());
        Flash::success(Lang::get('rainlab.user::lang.session.logout'));

        return Redirect::to($url);
    }

    /**
     * Returns whether or not we are logging in using email or username
     * @return mixed
     */
    public function signUp()
    {
        return Settings::get('login_attribute', 'email');
    }

    /**
     * Returns the logged in UserExtended object
     * @return mixed
     */
    public function user()
    {
        return UserUtil::convertToUserExtendedUser(UserUtil::getLoggedInUser());
    }

    /**
     * Returns an object of user settings
     * @return array
     */
    public function updateSettings()
    {
        return UserSettingsManager::init()->getUpdateable();
    }

    /**
     * Returns an object of user settings
     * @return array
     */
    public function createSettings()
    {
        return UserSettingsManager::init()->getRegisterable();
    }

}