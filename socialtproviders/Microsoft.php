<?php

namespace Clake\UserExtended\SocialProviders;

use Backend\Widgets\Form;
use Clake\UserExtended\Classes\Providers\MicrosoftProvider;
use Clake\UserExtended\SocialProviders\SocialProviderBase;
use Socialite;
use URL;

class Microsoft extends SocialConnectProviderBase {

    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'Microsoft';

    /**
     * Initialize the singleton free from constructor parameters.
     */
    protected function init() {
        parent::init();

        // Socialite uses config files for credentials but we want to pass from
        // our settings page - so override the login method for this provider
        Socialite::extend($this->driver, /**
                 *
                 */
                function($app) {
            $providers = \Tohur\SocialConnect\Models\Settings::instance()->get('providers', []);
            $providers['Microsoft']['redirect'] = URL::route('tohur_socialconnect_provider_callback', ['Microsoft'], true);
            $provider = Socialite::buildProvider(
                            MicrosoftProvider::class, (array) @$providers['Microsoft']
            );
            return $provider;
        });
    }

    public function isEnabled() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Microsoft']['enabled']);
    }

    public function isEnabledForBackend() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Microsoft']['enabledForBackend']);
    }

    public function extendSettingsForm(Form $form) {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/tohur/socialconnect/partials/backend/forms/settings/_microsoft_info.htm',
                'tab' => 'Microsoft',
            ],
            'providers[Microsoft][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with Microsoft?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'Microsoft',
            ],
            'providers[Microsoft][enabledForBackend]' => [
                'label' => 'Enabled on backend?',
                'type' => 'checkbox',
                'comment' => 'Can administrators log into the backend with Microsoft?',
                'default' => 'false',
                'span' => 'right',
                'tab' => 'Microsoft',
            ],
            'providers[Microsoft][redirect]' => [
                'label' => 'Redirect',
                'type' => 'text',
                'tab' => 'Microsoft',
            ],
            'providers[Microsoft][client_id]' => [
                'label' => 'Client ID',
                'type' => 'text',
                'tab' => 'Microsoft',
            ],
            'providers[Microsoft][client_secret]' => [
                'label' => 'Client Secret',
                'type' => 'text',
                'tab' => 'Microsoft',
            ],
                ], 'primary');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider() {
        return Socialite::with($this->driver)->redirect();
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