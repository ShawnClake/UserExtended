<?php

namespace Tohur\SocialConnect\SocialConnectProviders;

use Backend\Widgets\Form;
use Tohur\SocialConnect\Classes\MixerProvider;
use Tohur\SocialConnect\SocialConnectProviders\SocialConnectProviderBase;
use Socialite;
use URL;

class Mixer extends SocialConnectProviderBase {

    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'Mixer';

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
            $providers['Mixer']['redirect'] = URL::route('tohur_socialconnect_provider_callback', ['Mixer'], true);
            $provider = Socialite::buildProvider(
                            MixerProvider::class, (array) @$providers['Mixer']
            );
            return $provider;
        });
    }

    public function isEnabled() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Mixer']['enabled']);
    }

    public function isEnabledForBackend() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Mixer']['enabledForBackend']);
    }

    public function extendSettingsForm(Form $form) {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/tohur/socialconnect/partials/backend/forms/settings/_mixer_info.htm',
                'tab' => 'Mixer',
            ],
            'providers[Mixer][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with Mixer?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'Mixer',
            ],
            'providers[Mixer][enabledForBackend]' => [
                'label' => 'Enabled on backend?',
                'type' => 'checkbox',
                'comment' => 'Can administrators log into the backend with Mixer?',
                'default' => 'false',
                'span' => 'right',
                'tab' => 'Mixer',
            ],
            'providers[Mixer][redirect]' => [
                'label' => 'Redirect',
                'type' => 'text',
                'tab' => 'Mixer',
            ],
            'providers[Mixer][client_id]' => [
                'label' => 'Client ID',
                'type' => 'text',
                'tab' => 'Mixer',
            ],
            'providers[Mixer][client_secret]' => [
                'label' => 'Client Secret',
                'type' => 'text',
                'tab' => 'Mixer',
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