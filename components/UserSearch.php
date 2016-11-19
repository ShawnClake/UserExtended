<?php namespace Clake\Userextended\Components;

use Clake\UserExtended\Classes\UserManager;
use Cms\Classes\ComponentBase;

class UserSearch extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'User search',
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

        $results = UserManager::searchUsers($phrase);

        return $this->renderResults($results);
    }

    private function renderResults($results)
    {
        $content = $this->renderPartial('usersearch::search-results.htm', ['results' => $results]);
        return ['#userSearchResults' => $content];
    }

}