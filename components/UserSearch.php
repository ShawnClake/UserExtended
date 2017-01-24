<?php namespace Clake\Userextended\Components;

use Clake\UserExtended\Classes\UserManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\UserExtended;
use Cms\Classes\ComponentBase;

/**
 * TODO: Add better error checking as well as better support for custom partials
 */

/**
 * Class UserSearch
 * @package Clake\Userextended\Components
 * @deprecated Please use UserExtended.User
 */
class UserSearch extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'DEPRECIATED. User search',
            'description' => 'Provides an interface to search for other users'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onSearch()
    {
        $phrase = post('phrase');

        //$results = UserManager::searchUsers($phrase);

        $results = UserUtil::searchUsers($phrase);

        return $this->renderResults($results);
    }

    private function renderResults($results)
    {
        $content = $this->renderPartial('usersearch::search-results.htm', ['results' => $results]);
        return ['#userSearchResults' => $content];
    }

}