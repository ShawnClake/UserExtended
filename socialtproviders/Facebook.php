<?php

namespace Clake\UserExtended\SocialProviders;

use Backend\Widgets\Form;
use Clake\UserExtended\SocialProviders\SocialProviderBase;
use Socialite;
use Laravel\Socialite\Two\FacebookProvider;
use URL;

class Facebook extends SocialConnectProviderBase {

    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'facebook';

    /**
     * Initialize the singleton free from constructor parameters.
     */
    protected function init() {
        parent::init();

        // Socialite uses config files for credentials but we want to pass from
        // our settings page - so override the login method for this provider
        Socialite::extend($this->driver, function($app) {
            $providers = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
            $providers['Facebook']['redirect'] = URL::route('tohur_socialconnect_provider_callback', ['Facebook'], true);

            return Socialite::buildProvider(
                            FacebookProvider::class, (array) @$providers['Facebook']
            );
        });
    }

    public function isEnabled() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Facebook']['enabled']);
    }

    public function isEnabledForBackend() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Facebook']['enabledForBackend']);
    }

    public function extendSettingsForm(Form $form) {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/tohur/socialconnect/partials/backend/forms/settings/_facebook_info.htm',
                'tab' => 'Facebook',
            ],
            'providers[Facebook][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with Facebook?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'Facebook',
            ],
            'providers[Facebook][enabledForBackend]' => [
                'label' => 'Enabled on backend?',
                'type' => 'checkbox',
                'comment' => 'Can administrators log into the backend with Facebook?',
                'default' => 'false',
                'span' => 'right',
                'tab' => 'Facebook',
            ],
            'providers[Facebook][client_id]' => [
                'label' => 'App ID',
                'type' => 'text',
                'tab' => 'Facebook',
            ],
            'providers[Facebook][client_secret]' => [
                'label' => 'App Secret',
                'type' => 'text',
                'tab' => 'Facebook',
            ],
                ], 'primary');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider() {
        return Socialite::driver($this->driver)->scopes(['email'])->redirect();
    }

    /**
     * Handles redirecting off to the login provider
     *
     * @return array
     */
    public function handleProviderCallback() {
        $user = Socialite::driver($this->driver)->user();

        return (array) $user;
    }

}
