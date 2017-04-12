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

        $route = Route::where('route', $url)->where('enabled', true);

        if($route->count() <= 0)
            return '';

        $user = UserUtil::getLoggedInUser();
        if(!$user)
            return Redirect::intended($redirUrl);

        $restrictions = $route->first()->restrictions;

        $allowed = true;

        foreach($restrictions as $restriction)
        {
            if($restriction->type == RouteManager::UE_WHITELIST)
            {
                if(isset($restriction->user_id) && $user->id == $restriction->user_id)
                    return '';
                if(isset($restriction->role_id) && UserRoleManager::currentUser()->isInRole($restriction->role->code))
                    return '';
                if(isset($restriction->group_id) && UserGroupManager::currentUser()->isInGroup($restriction->group->code))
                    return '';
                if(isset($restriction->ip) && $_SERVER['REMOTE_ADDR'] == $restriction->ip)
                    return '';
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
        //echo json_encode($restrictions);

        if($allowed)
            return '';

        return Redirect::intended($redirUrl);
    }

}