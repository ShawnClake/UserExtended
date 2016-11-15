<?php namespace Clake\Userextended\Components;

use Clake\UserExtended\Classes\FriendsManager;
use Clake\UserExtended\Classes\UserManager;
use Cms\Classes\ComponentBase;

class UserList extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'User list',
            'description' => 'Provides an interface for listing users'
        ];
    }

    public function defineProperties()
    {
        return [
            'type' => [
                'title'       => 'Type',
                'type'        => 'dropdown',
                'default'     => 'random',
                'placeholder' => 'Select list type',
                'options'     => ['random'=>'Random', 'user'=>'User']
            ],
            'maxItems' => [
                'title'             => 'Max items',
                'description'       => 'The most amount of users to show',
                'default'           => 5,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Items property can contain only numeric symbols'
            ],
            'userID' => [
                'title'             => 'User ID',
                'description'       => 'The User ID for a single user. Set to 0 for self',
                'default'           => 0,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The user ID property can contain only numeric symbols'
            ]
        ];
    }

    /**
     * Provides a variable to the page for listing users
     * @return array|\Illuminate\Support\Collection
     */
    public function userlist()
    {

        $list = [];

        $properties = $this->getProperties();

        if($properties['type'] == "random")
        {
            $list = UserManager::getRandomUserSet($properties['maxItems']);
        }
        return $list;

    }

    /**
     * AJAX call for when someone wants to send a friend request
     */
    public function onFriendUser()
    {

        $userid = post('id');

        FriendsManager::sendFriendRequest($userid);

    }

}