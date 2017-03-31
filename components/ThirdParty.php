<?php namespace Clake\Userextended\Components;

use Clake\UserExtended\Plugin;
use Cms\Classes\ComponentBase;
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
            ],
            'maxItems' => [
                'title'             => 'Max items',
                'description'       => 'Max items to show in a list. 0=unlimited',
                'default'           => 5,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Items property can contain only numeric symbols'
            ],
            'paramCode' => [
                'title'       => 'User ID URL parameter',
                'description' => 'Specifies a user ID to generate a list for. blank=logged in user',
                'type'        => 'string',
                'default'     => ''
            ],
            'profilePage' => [
                'title'       => 'Profile Page',
                'description' => 'The page to redirect to for user profiles.',
                'type'        => 'dropdown',
                'default'     => 'user/profile'
            ]

        ];
    }

    /**
     * Used for properties dropdown menu
     * @return array
     */
    public function getTypeOptions()
    {
        return ['disqus' => 'Disqus'];
    }

    /**
     * Injects assets
     */
    public function onRun()
    {
        Plugin::injectAssets($this);
        //$this->addJs('/plugins/clake/userextended/assets/js/friends.css');
    }

    public function disqus()
    {
        return 'userextended';
    }

}