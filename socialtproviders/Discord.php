<?php

namespace Clake\UserExtended\SocialProviders;

use Backend\Widgets\Form;
use Clake\UserExtended\Classes\Providers\DiscordProvider;
use Clake\UserExtended\SocialProviders\SocialProviderBase;
use Socialite;
use URL;

class Discord extends SocialConnectProviderBase {

    use \October\Rain\Support\Traits\Singleton;

    protected $driver = 'Discord';

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
            $providers['Discord']['redirect'] = URL::route('tohur_socialconnect_provider_callback', ['Discord'], true);
            $provider = Socialite::buildProvider(
                            DiscordProvider::class, (array) @$providers['Discord']
            );
            return $provider;
        });
    }

    public function isEnabled() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Discord']['enabled']);
    }

    public function isEnabledForBackend() {
        $providers = $this->settings->get('providers', []);

        return !empty($providers['Discord']['enabledForBackend']);
    }

    public function extendSettingsForm(Form $form) {
        $form->addFields([
            'noop' => [
                'type' => 'partial',
                'path' => '$/tohur/socialconnect/partials/backend/forms/settings/_discord_info.htm',
                'tab' => 'Discord',
            ],
            'providers[Discord][enabled]' => [
                'label' => 'Enabled on frontend?',
                'type' => 'checkbox',
                'comment' => 'Can frontend users log in with Discord?',
                'default' => 'true',
                'span' => 'left',
                'tab' => 'Discord',
            ],
            'providers[Discord][enabledForBackend]' => [
                'label' => 'Enabled on backend?',
                'type' => 'checkbox',
                'comment' => 'Can administrators log into the backend with Discord?',
                'default' => 'false',
                'span' => 'right',
                'tab' => 'Discord',
            ],
            'providers[Discord][redirect]' => [
                'label' => 'Redirect',
                'type' => 'text',
                'tab' => 'Discord',
            ],
            'providers[Discord][client_id]' => [
                'label' => 'Client ID',
                'type' => 'text',
                'tab' => 'Discord',
            ],
            'providers[Discord][client_secret]' => [
                'label' => 'Client Secret',
                'type' => 'text',
                'tab' => 'Discord',
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
