<?php namespace Clake\Userextended\Components;

use Clake\UserExtended\Classes\RoleManager;
use Clake\UserExtended\Classes\RouteManager;
use Clake\UserExtended\Classes\UserGroupManager;
use Clake\UserExtended\Classes\UserRoleManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\Route;
use Clake\Userextended\Models\RouteRestriction;
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
            'description' => 'Put this on your layouts to utilize the Route Restriction feature of UE'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'title'       => 'Redirect',
                'description' => 'The page to redirect to when a user can\'t access this page.',
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
     */
    public function onRun()
    {
        $redirUrl = $this->property('redirect');

        Plugin::injectAssets($this);
        $url = $this->page->url;

        // If no restrictions exist, then we are good to go. Return positively.
        if(!$this->doRestrictionsExist($url))
            return '';

        // If the user isn't logged in, then no chance they can access a restricted page.
        $user = UserUtil::getLoggedInUser();
        if(!$user)
            return Redirect::intended($redirUrl);

        // Now we start checking for whether the user should be allowed to access the page starting at the most parent restrictions
        $allowed = true;

        $parents = substr_count($url, '/') - 1;

        $offset = 1;

        for($i = 0; $i < $parents; $i++)
        {
            $length = strpos($url, '/', $offset);
            $offset += $length;
            $subUrl = substr($url, 0, $length);

            $route = Route::where('route', $subUrl)->where('enabled', true)->where('cascade', true);

            if($route->count() > 0)
            {
                $state = $this->isRouteAllowed($route, $user);
                if($state !== null)
                    $allowed = $state;
            }
        }

        $route = Route::where('route', $url)->where('enabled', true);

        if($route->count() > 0)
        {
            $state = $this->isRouteAllowed($route, $user);
            if($state !== null)
                $allowed = $state;
        }

        if($allowed)
            return '';

        return Redirect::intended($redirUrl);
    }

    protected function doRestrictionsExist($url)
    {
        $possibles = substr_count($url, '/');

        $url .= '/';

        for($i = 0; $i < $possibles; $i++)
        {
            $url = substr($url, 0, strrpos($url, '/'));

            $query = Route::where('route', $url)->where('enabled', true);

            if($i != 0)
            {
                $query->where('cascade', true);

            }

            if($query->count() > 0)
                return true;
        }

        return false;
    }

    protected function isRouteAllowed($route, $user)
    {
        $restrictions = $route->first()->restrictions;

        $allowed = null;

        foreach($restrictions as $restriction)
        {
            if($restriction->type == RouteManager::UE_WHITELIST)
            {
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