<?php

namespace Clake\UserExtended\SocialProviders;

use Backend\Widgets\Form;
use Clake\UserExtended\SocialProviders\SocialProviderBase;
use Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use URL;

class Google extends SocialConnectProviderBase {

    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'google';

    /**
     * Initialize the singleton free from constructor parameters.
     */
    protected function init() {
        parent::init();

        // Socialite uses config files for credentials but we want to pass from
        // our settings page - so override the login method for this provider
        Socialite::extend($this->driver, function($app) {
            $providers = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
            $providers['Google']['redirect'] = URL::route('tohur_socialconnect_provider_callback', ['Google'], true);

            return Socialite::buildProvider(
                            GoogleProvider::class, (array) @$providers['Google']
            );
        });
    }

    public function isEnabled() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Google']['enabled']);
    }

    public function isEnabledForBackend() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Google']['enabledForBackend']);
    }

    public function extendSettingsForm(Form $form) {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/tohur/socialconnect/partials/backend/forms/settings/_google_info.htm',
                'tab' => 'Google',
            ],
            'providers[Google][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with Google?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'Google',
            ],
            'providers[Google][enabledForBackend]' => [
                'label' => 'Enabled on backend?',
                'type' => 'checkbox',
                'comment' => 'Can administrators log into the backend with Google?',
                'default' => 'false',
                'span' => 'right',
                'tab' => 'Google',
            ],
            'providers[Google][app_name]' => [
                'label' => 'Application Name',
                'type' => 'text',
                'default' => 'Social Login',
                'comment' => 'This appears on the Google login screen. Usually your site name.',
                'tab' => 'Google',
            ],
            'providers[Google][client_id]' => [
                'label' => 'Client ID',
                'type' => 'text',
                'tab' => 'Google',
            ],
            'providers[Google][client_secret]' => [
                'label' => 'Client Secret',
                'type' => 'text',
                'tab' => 'Google',
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
        return (array) Socialite::driver($this->driver)->user();
    }

}
