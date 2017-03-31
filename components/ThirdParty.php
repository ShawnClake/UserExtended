<?php namespace Clake\Userextended\Components;

use Clake\UserExtended\Classes\IntegrationManager;
use Clake\UserExtended\Classes\UserManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\IntegratedUser;
use Clake\Userextended\Models\Settings;
use Clake\UserExtended\Plugin;
use Cms\Classes\ComponentBase;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Support\Facades\Redirect;
use Page;

/**
 * User Extended by Shawn Clake
 * Class Friends
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Components
 */
class ThirdParty extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => '3rd Party',
            'description' => '3rd Party Integrations: Disqus, Facebook, Twitter, etc.'
        ];
    }

    public function defineProperties()
    {
        return [
            'type' => [
                'title'       => 'Integration',
                'type'        => 'dropdown',
                'default'     => 'disqus',
                'placeholder' => 'Select integration type',
            ]
        ];
    }

    /**
     * Returns the integration type
     */
    public function type()
    {
        return $this->property('type');
    }

    /**
     * Used for properties dropdown menu
     * @return array
     */
    public function getTypeOptions()
    {
        return ['disqus' => 'Disqus', 'facebook-login' => 'Facebook Login'];
    }

    /**
     * Injects assets
     */
    public function onRun()
    {
        Plugin::injectAssets($this);
        //$this->addJs('/plugins/clake/userextended/assets/js/friends.css');
    }

    public function enableDisqus()
    {
        return Settings::get('enable_disqus');
    }

    public function disqus()
    {
        return Settings::get('disqus_shortname');
    }

    public function enableFacebook()
    {
        return Settings::get('enable_facebook');
    }

    public function facebook()
    {
        return Settings::get('facebook_appid');
    }

    public function onFacebookAuth()
    {
        $data = post();

        if(UserUtil::getLoggedInUser() != null)
            return Redirect::to('update');

        if($user = IntegrationManager::getUser($data['id']))
        {
            // If user already exists
            //echo 'already exists';
            UserManager::loginUserObj($user);

            return Redirect::to('update');
        }

        $password = openssl_random_pseudo_bytes(16);

        $registration = [
            'email' => $data['email'],
            'username' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'password' => $password,
            'password_confirmation' => $password
        ];

        if(!($user = UserManager::registerUser($registration)))
            return false;

        $reflection = new \ReflectionClass($user);

        if($reflection->getShortName() == 'Validator')
        {
            throw new ValidationException($user);
            //Flash::error($user->messages());
            //return false;
        }

        $integration = IntegrationManager::createUser($data['id'], $user->id, IntegrationManager::UE_INTEGRATIONS_FACEBOOK);

        UserManager::loginUserObj($user);

        return Redirect::to('update');
        //echo 'caught';

    }

}