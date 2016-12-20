<?php namespace Clake\Userextended\Components;

use Clake\Pusher\Classes\Pusher;
use Clake\UserExtended\Classes\FriendsManager;
use Clake\UserExtended\Classes\UserUtil;
use Cms\Classes\ComponentBase;

/**
 * TODO: Force conventions
 * TODO: Improve error checking
 */

/**
 * Class ListFriendRequests
 * @package Clake\Userextended\Components
 */
class ListFriendRequests extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Friend request list',
            'description' => 'Lists a users received friend requests'
        ];
    }

    public function defineProperties()
    {
        return [
            'maxItems' => [
                'title'             => 'Max items',
                'description'       => 'The most amount of friend requests to show',
                'default'           => 5,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Items property can contain only numeric symbols'
            ],
        ];
    }


    /**
     * Returns a list of users who have requested you to be their friend
     *
     * @return \Illuminate\Support\Collection
     */
    public function friendrequests()
    {
        $limit = $this->property('maxItems');

        return FriendsManager::listReceivedFriendRequests(null, $limit);
    }

    /**
     * AJAX call when a button is clicked to accept a friend request
     */
    public function onAccept()
    {
        $userid = post('id');

        if($userid != null)
            FriendsManager::acceptRequest($userid);

        //$data = UserUtil::getLoggedInUser()->toArray();
        //Pusher::init()->trigger('private-mychannel', 'tests', $data);

    }

    public function onDecline()
    {
        $userid = post('id');

        if($userid != null)
            FriendsManager::declineRequest($userid);


    }

}