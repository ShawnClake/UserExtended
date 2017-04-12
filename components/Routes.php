<?php namespace Clake\Userextended\Components;

use Clake\UserExtended\Classes\RouteManager;
use Clake\UserExtended\Classes\UserGroupManager;
use Clake\UserExtended\Classes\UserRoleManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\Route;
use Clake\UserExtended\Plugin;
use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Illuminate\Support\Facades\Redirect;

/**
 * User Extended by Shawn Clake
 * Class Routes
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Components
 */
class Routes extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Routes',
            'description' => 'Put this on your layouts to utilize the Route Restriction feature'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'title'       => 'Redirect',
                'description' => 'The page to redirect to when a user is denied access to this page.',
                'type'        => 'dropdown',
                'default'     => '/'
            ],
        ];
    }

    /**
     * Used for properties dropdown menu
     * @return mixed
     */
    public function getRedirectOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Injects assets
     * Will see if the route needs to be denied or if its allowed for a person accessing it
     */
    public function onRun()
    {
        Plugin::injectAssets($this);

        $redirUrl = $this->property('redirect');

        $url = $this->page->url;

        // If no restrictions exist, then we are good to go. Return positively.
        if(!$this->doRestrictionsExist($url))
            return '';

        // If the user isn't logged in, then no chance they can access a restricted page.
        $user = UserUtil::getLoggedInUser();
        if(!$user)
            return Redirect::intended($redirUrl);

        // Now we start checking for whether the user should be allowed to access the page starting at
        // the most parent restrictions
        $allowed = true;

        $parents = substr_count($url, '/') - 1;

        $offset = 1;

        // Uses a front to back URL scan. Starts with the top most parent route and works to the child route,
        // but does not process the child route
        for($i = 0; $i < $parents; $i++) {
            $length = strpos($url, '/', $offset);
            $offset += $length;
            $subUrl = substr($url, 0, $length);

            $route = Route::where('route', $subUrl)->where('enabled', true)->where('cascade', true);

            if($route->count() > 0) {
                $state = $this->isRouteAllowed($route, $user);
                if($state !== null)
                    $allowed = $state;
            }
        }

        // Child route
        $route = Route::where('route', $url)->where('enabled', true);

        if($route->count() > 0) {
            $state = $this->isRouteAllowed($route, $user);
            if($state !== null)
                $allowed = $state;
        }

        if($allowed)
            return '';

        return Redirect::intended($redirUrl);
    }

    /**
     * Returns true if child or cascading parent routes exist for a route
     * @param $url
     * @return bool
     */
    protected function doRestrictionsExist($url)
    {
        // Uses a back to front URL scan. Starts with the child route and works to the top most parent route
        $possibles = substr_count($url, '/');

        $url .= '/';

        for($i = 0; $i < $possibles; $i++) {
            $url = substr($url, 0, strrpos($url, '/'));
            $query = Route::where('route', $url)->where('enabled', true);

            if($i != 0)
                $query->where('cascade', true);

            if($query->count() > 0)
                return true;
        }

        return false;
    }

    /**
     * Determines whether a route allows, denies, or affects a user trying to access it
     * Returns null if the route does not effect the person trying to access it
     * Returns true if the route allows the person trying to access it
     * Returns false if the route denies the person trying to access it
     * @param $route
     * @param $user
     * @return bool|null
     */
    protected function isRouteAllowed($route, $user)
    {
        $restrictions = $route->first()->restrictions;

        $allowed = null;

        foreach($restrictions as $restriction) {
            if($restriction->type == RouteManager::UE_WHITELIST) {
                if(isset($restriction->user_id) && $user->id == $restriction->user_id)
                    return true;
                if(isset($restriction->role_id) && UserRoleManager::currentUser()->isInRole($restriction->role->code))
                    return true;
                if(isset($restriction->group_id) && UserGroupManager::currentUser()->isInGroup($restriction->group->code))
                    return true;
                if(isset($restriction->ip) && $_SERVER['REMOTE_ADDR'] == $restriction->ip)
                    return true;
            } else {
                if(isset($restriction->user_id) && $user->id == $restriction->user_id)
                    $allowed = false;
                if(isset($restriction->role_id) && UserRoleManager::currentUser()->isInRole($restriction->role->code))
                    $allowed = false;
                if(isset($restriction->group_id) && UserGroupManager::currentUser()->isInGroup($restriction->group->code))
                    $allowed = false;
                if(isset($restriction->ip) && $_SERVER['REMOTE_ADDR'] == $restriction->ip)
                    $allowed = false;
            }
        }

        return $allowed;
    }

}