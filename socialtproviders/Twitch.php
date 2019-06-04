<?php

namespace Tohur\SocialConnect\SocialConnectProviders;

use Backend\Widgets\Form;
use Tohur\SocialConnect\Classes\TwitchProvider;
use Tohur\SocialConnect\SocialConnectProviders\SocialConnectProviderBase;
use Socialite;
use URL;

class Twitch extends SocialConnectProviderBase {

    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'Twitch';

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
            $providers['Twitch']['redirect'] = URL::route('tohur_socialconnect_provider_callback', ['Twitch'], true);
            $provider = Socialite::buildProvider(
                            TwitchProvider::class, (array) @$providers['Twitch']
            );
            return $provider;
        });
    }

    public function isEnabled() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Twitch']['enabled']);
    }

    public function isEnabledForBackend() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Twitch']['enabledForBackend']);
    }

    public function extendSettingsForm(Form $form) {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/tohur/socialconnect/partials/backend/forms/settings/_twitch_info.htm',
                'tab' => 'Twitch',
            ],
            'providers[Twitch][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with Twitch?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'Twitch',
            ],
            'providers[Twitch][enabledForBackend]' => [
                'label' => 'Enabled on backend?',
                'type' => 'checkbox',
                'comment' => 'Can administrators log into the backend with Twitch?',
                'default' => 'false',
                'span' => 'right',
                'tab' => 'Twitch',
            ],
            'providers[Twitch][redirect]' => [
                'label' => 'Redirect',
                'type' => 'text',
                'tab' => 'Twitch',
            ],
            'providers[Twitch][client_id]' => [
                'label' => 'Client ID',
                'type' => 'text',
                'tab' => 'Twitch',
            ],
            'providers[Twitch][client_secret]' => [
                'label' => 'Client Secret',
                'type' => 'text',
                'tab' => 'Twitch',
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
