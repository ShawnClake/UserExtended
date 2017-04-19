<?php namespace Clake\UserExtended;

use Backend\Classes\Controller;
use Clake\UserExtended\Classes\FriendsManager;
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
            'description' => 'Adds roles, friends, profiles, and utility functions to the Rainlab User plugin',
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

    public function registerListColumnTypes()
    {
        return [
            'listdropdown' => [$this, 'getListChoice']
        ];
    }

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
            $manager->addSideMenuItems('RainLab.User', 'user', [
                'roles' => [
                    'label' => 'Role Manager',
                    'url'   => Backend::url('clake/userextended/roles/manage'),
                    'icon'  => 'icon-pencil',
                    'order' => 700
                ],
                'users-side' => [
                    'label' => 'Users',
                    'url'   => Backend::url('rainlab/user/users'),
                    'icon'  => 'icon-user',
                    'order' => 100
                ],
                'fields' => [
                    'label' => 'Field Manager',
                    'url'   => Backend::url('clake/userextended/fields/manage'),
                    'icon'  => 'icon-pencil-square-o',
                    'order' => 600
                ],
                'routes' => [
                    'label' => 'Routes',
                    'url'   => Backend::url('clake/userextended/routes/index'),
                    'icon'  => 'icon-eye-slash',
                    'order' => 300
                ],
                'timezones' => [
                    'label' => 'Timezones',
                    'url'   => Backend::url('clake/userextended/timezones/index'),
                    'icon'  => 'icon-clock-o',
                    'order' => 200
                ],
                'friends' => [
                    'label' => 'Friends',
                    'url'   => Backend::url('clake/userextended/friends/index'),
                    'icon'  => 'icon-users',
                    'order' => 500
                ],
                'modules' => [
                    'label' => 'Modules',
                    'url'   => Backend::url('clake/userextended/modules/manage'),
                    'icon'  => 'icon-puzzle-piece',
                    'order' => 900
                ],
            ]);

            $manager->addSideMenuItems('October.Cms', 'cms', [
                /*'routes' => [
                    'label' => 'Routes',
                    'url'   => Backend::url('clake/userextended/routes/preview'),
                    'icon'  => 'icon-eye-slash',
                    //'order' => 600
                ],*/

            ]);

        });

        // TODO: Try and see if we can hack in some code for injecting fields into the user controller.
        // TODO: The reason we can't right now, is although the field would appear the user controller
        // TODO: Wouldn't know how to save them as they get array'd and saved as JSON.
        /*$settings =

        Event::listen('backend.form.extendFields', function($widget) {

            // Only for the User controller
            if (!$widget->getController() instanceof \RainLab\User\Controllers\Users) {
                return;
            }

            // Only for the User model
            if (!$widget->model instanceof \RainLab\User\Models\User) {
                return;
            }

            // Add an extra birthday field
            $widget->addFields([
                'birthday' => [
                    'label'   => 'Birthday',
                    'comment' => 'Select the users birthday',
                    'type'    => 'datepicker'
                ]
            ]);

        });*/

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

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'UserExtended Settings',
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
        return [];
    }

    /**
     * Registers back-end navigation items for this plugin.
     * @return array
     */
    public function registerNavigation()
    {
        return array_merge(
            UserExtended::getNavigation(),
            []
        );
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

        /*
         * Handles injecting JS and CSS assets
         */
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
