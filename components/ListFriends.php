<?php namespace Clake\Userextended\Components;

use Clake\UserExtended\Classes\FriendsManager;
use Clake\UserExtended\Classes\UserManager;
use Cms\Classes\ComponentBase;

/**
 * TODO: Improve error checking
 */

/**
 * Class ListFriends
 * @package Clake\Userextended\Components
 */
class ListFriends extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Friends List',
            'description' => 'List a users friends'
        ];
    }

    public function defineProperties()
    {
        return [
            'maxItems' => [
                'title'             => 'Max items',
                'description'       => 'The most amount of friends to show',
                'default'           => 5,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Items property can contain only numeric symbols'
            ]
        ];
    }

    /**
     * Returns a variable to the page which lists a users friends.
     *
     */
    public function friends()
    {

        $limit = $this->property('maxItems');

        return FriendsManager::listFriends($limit);

    }

    /**
     * Lists a random set of users. Useful for 'suggestions'
     * @return \Illuminate\Support\Collection
     */
    public function listRandomUsers()
    {
        return UserManager::getRandomUserSet(5);
    }

    /**
     * AJAX call to delete a friend
     */
    public function onDelete()
    {
        $userid = post('id');

        if($userid != null)
            FriendsManager::deleteFriend($userid);
    }

}