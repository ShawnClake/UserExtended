<?php namespace Clake\UserExtended;

use Clake\UserExtended\Classes\UserExtended;
use System\Classes\PluginBase;
use Event;
use Backend;
use System\Classes\SettingsManager;

/**
 * User Extended Core by Shawn Clake
 * Major Contributors: Quinn Bast
 *
 * User Extended is licensed under the MIT license.
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @version 2.2.00 User Extended Core
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake
 * @link http://shawnclake.com
 *
 * Major Contributors:
 * @link https://github.com/QuinnBast
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE
 *
 * Class Plugin
 * @package Clake\UserExtended
 */
class Plugin extends PluginBase
{
    /**
     * An array containing the plugins which UserExtended depnds on
     * @var array
     */
    public $require = [
        'RainLab.User'
    ];

    /**
     * Returns information about this plugin.
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'UserExtended',
            'description' => 'Adds roles, friends, profiles, route tracking, and utility functions to the Rainlab User plugin',
            'author'      => 'clake',
            'icon'        => 'icon-user-plus',
            'homepage'    => 'https://github.com/ShawnClake/UserExtended'
        ];
    }

    /**
     * Adds twig filters and functions
     * @return array
     */
    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'timezonify' => [
                    'Clake\Userextended\Classes\TimezoneHandler',
                    'twigTimezoneAdjustment'
                ],
                'relative' => [
                    'Clake\Userextended\Classes\TimezoneHandler',
                    'twigRelativeTimeString'
                ]
            ]
        ];
    }

    /**
     * Registers column types for a model's columns.yaml to use
     * @return array
     */
    public function registerListColumnTypes()
    {
        return [
            'listdropdown' => [$this, 'getListChoice']
        ];
    }

    /**
     * Generates a string based on the chosen dropdown choice.
     * The key of the dropdown choice is compared to an array return by a class and function specified in the columns.yaml.
     * The value at this key in the returned array is what is returned.
     * @param $value
     * @param $column
     * @param $record
     * @return string
     */
    public function getListChoice($value, $column, $record)
    {
        $string = '';

        $class = $column->config['class'];
        $function = $column->config['function'];

        if(method_exists($class, $function))
        {
            $class = new $class();
            $array = $class->$function();
            $string = $array[$value];
        }

        return $string;
    }


    /**
     * Register method, called when the plugin is first registered.
     * @return void
     */
    public function register()
    {
        /*
         * Registers the UE scaffolding command for creating modules
         */
        $this->registerConsoleCommand('create:uemodule', 'Clake\UserExtended\Console\CreateUEModule');

        /*
         * Registers the UserExtended core module
         */
        Module::register();
    }

    /**
     * Boot method, called right before the request route.
     * @return array
     */
    public function boot()
    {
        /*
         * Boots the modules which were registered with UserExtended
         */
        UserExtended::boot();

        /*
         * Event listener adds the Group Manager button to the side bar of the User backend UI.
         */
        Event::listen('backend.menu.extendItems', function ($manager)
        {
            $navigation = array_merge(
                UserExtended::getNavigation(),
                []
            );
            $manager->addSideMenuItems('RainLab.User', 'user', $navigation);
        });

        return [];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     * @return array
     */
    public function registerComponents()
    {
        return array_merge(
            UserExtended::getComponents(),
            []
        );
    }

    /**
     * Registers the settings model for User Extended
     * @return array
     */
    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'User Extended',
                'description' => 'Manage user extended settings.',
                'category'    => SettingsManager::CATEGORY_USERS,
                'icon'        => 'icon-cog',
                'class'       => 'Clake\Userextended\Models\Settings',
                'order'       => 100,
                'keywords'    => 'security user extended',
                'permissions' => ['']
            ]
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'clake.userextended.roles.view' => [
                'label' => 'View Roles',
                'tab' => 'User Extended'
            ],
            'clake.userextended.groups.view' => [
                'label' => 'View Groups',
                'tab' => 'User Extended'
            ],
            'clake.userextended.role_users.view' => [
                'label' => 'View Users in Roles',
                'tab' => 'User Extended'
            ],
            'clake.userextended.group_users.view' => [
                'label' => 'View Users in Groups',
                'tab' => 'User Extended'
            ],
            'clake.userextended.roles.manage' => [
                'label' => 'Manage Roles',
                'tab' => 'User Extended'
            ],
            'clake.userextended.groups.manage' => [
                'label' => 'Manage Groups',
                'tab' => 'User Extended'
            ],
            'clake.userextended.role_users.manage' => [
                'label' => 'Manage Users in Roles',
                'tab' => 'User Extended'
            ],
            'clake.userextended.group_users.manage' => [
                'label' => 'Manage Users in Groups',
                'tab' => 'User Extended'
            ],
            'clake.userextended.modules.view' => [
                'label' => 'View Modules',
                'tab' => 'User Extended'
            ],
            'clake.userextended.modules.manage' => [
                'label' => 'Manage Modules',
                'tab' => 'User Extended'
            ],
            'clake.userextended.timezones.view' => [
                'label' => 'View Timezones',
                'tab' => 'User Extended'
            ],
            'clake.userextended.timezones.manage' => [
                'label' => 'Manage Timezones',
                'tab' => 'User Extended'
            ],
            'clake.userextended.friends.view' => [
                'label' => 'View Friends',
                'tab' => 'User Extended'
            ],
            'clake.userextended.friends.manage' => [
                'label' => 'Manage Friends',
                'tab' => 'User Extended'
            ],
            'clake.userextended.routes.view' => [
                'label' => 'View Routes',
                'tab' => 'User Extended'
            ],
            'clake.userextended.routes.manage' => [
                'label' => 'Manage Routes',
                'tab' => 'User Extended'
            ],
            'clake.userextended.fields.view' => [
                'label' => 'View Fields',
                'tab' => 'User Extended'
            ],
            'clake.userextended.fields.manage' => [
                'label' => 'Manage Fields',
                'tab' => 'User Extended'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     * @return array
     */
    public function registerNavigation()
    {
        return [];
    }

    /**
     * Injects assets from modules. Also overrides the defaults presented in general.js and general.css
     * @param $component
     */
    public static function injectAssets($component)
    {
        // Cant move these out because then the defaults wouldn't have top priority.
        $component->addJs('/plugins/clake/userextended/assets/js/general.js');
        $component->addCss('/plugins/clake/userextended/assets/css/general.css');

        // Handles injecting JS and CSS assets
        $assets = UserExtended::getAssets();

        foreach ($assets as $asset) {
            $type = trim(substr($asset, strrpos($asset, '.') + 1));

            if ($type == 'js') {
                $component->addJs($asset);
            }

            else if ($type == 'css') {
                $component->addCss($asset);
            }
        }
    }

    /**
     * Registers mail templates
     * @return array
     */
    public function registerMailTemplates()
    {
        return [
            'clake.userextended::mail.on_group_role_changed'    => 'Notify that the users group was changed',
            'clake.userextended::mail.received_friend_request'  => 'Friend request',
            'clake.userextended::mail.received_profile_comment' => 'New comment on user profile',
            'clake.userextended::mail.register'                 => 'Registration confirmation email'
        ];
    }
}
