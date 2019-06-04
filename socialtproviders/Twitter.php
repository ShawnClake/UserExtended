<?php

namespace Tohur\SocialConnect\SocialConnectProviders;

use Backend\Widgets\Form;
use Tohur\SocialConnect\SocialConnectProviders\SocialConnectProviderBase;
use Socialite;
use Laravel\Socialite\One\TwitterProvider;
use League\OAuth1\Client\Server\Twitter as TwitterServer;
use URL;

class Twitter extends SocialConnectProviderBase {

    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'twitter';

    /**
     * Initialize the singleton free from constructor parameters.
     */
    protected function init() {
        parent::init();

        // Socialite uses config files for credentials but we want to pass from
        // our settings page - so override the login method for this provider
        Socialite::extend($this->driver, function($app) {
            $providers = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
            $providers['Twitter']['redirect'] = URL::route('tohur_socialconnect_provider_callback', ['Twitter'], true);

            return new TwitterProvider(
                    app()->request, new TwitterServer($providers['Twitter'])
            );
            return Socialite::buildProvider(
                            TwitterProvider::class, (array) @$providers['Twitter']
            );
        });
    }

    public function isEnabled() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Twitter']['enabled']);
    }

    public function isEnabledForBackend() {
        //$providers = $this->settings->get('providers', []);
        //
        //return !empty($providers['Twitter']['enabledForBackend']);

        return false;
    }

    public function extendSettingsForm(Form $form) {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/tohur/socialconnect/partials/backend/forms/settings/_twitter_info.htm',
                'tab' => 'Twitter',
            ],
            'providers[Twitter][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with Twitter?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'Twitter',
            ],
            //'providers[Twitter][enabledForBackend]' => [
            //    'label' => 'Enabled on backend?',
            //    'type' => 'checkbox',
            //    'comment' => 'Can administrators log into the backend with Twitter?',
            //    'default' => 'false',
            //    'span' => 'right',
            //    'tab' => 'Twitter',
            //],
            'providers[Twitter][identifier]' => [
                'label' => 'API Key',
                'type' => 'text',
                'tab' => 'Twitter',
            ],
            'providers[Twitter][secret]' => [
                'label' => 'API Secret',
                'type' => 'text',
                'tab' => 'Twitter',
            ],
                ], 'primary');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider() {
        return Socialite::driver($this->driver)->redirect();
    }

    /**
     * Handles redirecting off to the login provider
     *
     * @return array
     */
    public function handleProviderCallback() {
        $user = Socialite::driver($this->driver)->user();

        if (empty($user->email))
            $user->email = $user->nickname . '@dev.null';

        return (array) $user;
    }

}
