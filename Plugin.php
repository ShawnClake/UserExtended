<?php namespace Clake\UserExtended;

use System\Classes\PluginBase;

/**
 * UserExtended Plugin Information File
 */
class Plugin extends PluginBase
{


    public $require = ['RainLab.User'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'UserExtended',
            'description' => 'Adds roles, friends, and utility functions to the Rainlab User plugin',
            'author'      => 'clake',
            'icon'        => 'icon-user-plus'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        //return []; // Remove this line to activate

        return [
            'Clake\UserExtended\Components\UserGroups' => 'usergroups',
            'Clake\UserExtended\Components\ListFriends' => 'friends',
            'Clake\UserExtended\Components\UserList' => 'userlist',
            'Clake\UserExtended\Components\ListFriendRequests' => 'friendrequests',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate
    }

}
