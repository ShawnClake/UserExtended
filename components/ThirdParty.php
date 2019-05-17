<?php

namespace Clake\Userextended\Components;

use Session;
use URL;
use ViewErrorBag;
use Clake\UserExtended\Classes\IntegrationManager;
use Clake\UserExtended\Classes\UserManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\Settings;
use Clake\UserExtended\Plugin;
use Tohur\SocialConnect\Models\Settings as SocialSettings;
use Tohur\SocialConnect\Classes\ProviderManager;
use Cms\Classes\ComponentBase;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Support\Facades\Redirect;
use Page;

/**
 * User Extended by Shawn Clake
 * Class ThirdParty
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Components
 */
class ThirdParty extends ComponentBase {

    public function componentDetails() {
        return [
            'name' => '3rd Party',
            'description' => '3rd Party Integrations: Disqus, Facebook, Twitter, etc.'
        ];
    }

    public function defineProperties() {
        return [
            'type' => [
                'title' => 'Integration',
                'type' => 'dropdown',
                'default' => 'disqus',
                'placeholder' => 'Select integration type',
            ]
        ];
    }

    /**
     * Returns the integration type
     */
    public function type() {
        return $this->property('type');
    }

    /**
     * Used for properties dropdown menu
     * @return array
     */
    public function getTypeOptions() {
        return ['disqus' => 'Disqus', 'sso-login' => 'Social Login'];
    }

    /**
     * Injects assets
     */
    public function onRun() {
        Plugin::injectAssets($this);
        $providers = ProviderManager::instance()->listProviders();

        $social_connect_links = [];
        foreach ($providers as $provider_class => $provider_details)
            if ($provider_class::instance()->isEnabled())
                $social_connect_links[$provider_details['alias']] = URL::route('tohur_socialconnect_provider', [$provider_details['alias']]);

        $this->page['social_connect_links'] = $social_connect_links;

        $this->page['errors'] = Session::get('errors');
    }

    /**
     * Returns whether or not Disqus is enabled
     * @return mixed
     */
    public function enableDisqus() {
        return Settings::get('enable_disqus');
    }

    /**
     * Returns the disqus site shortname
     * @return mixed
     */
    public function disqus() {
        return Settings::get('disqus_shortname');
    }

    /**
     * Returns whether or not Social Login is Enabled is enabled
     * @return mixed
     */
    public function enableSSO() {
        return Settings::get('enable_sso');
    }

}
