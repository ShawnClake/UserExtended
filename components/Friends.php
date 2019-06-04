<?php

namespace Clake\Userextended\Components;

use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\Settings;
use Clake\UserExtended\Plugin;
use Cms\Classes\ComponentBase;
use Clake\UserExtended\Classes\FriendsManager;
use Page;

/**
 * User Extended by Shawn Clake
 * Class Friends
 * User Extended is licensed under the MIT license.
 *
 * TODO: Do we need the paramCode property?
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Components
 */
class Friends extends ComponentBase {

    public function componentDetails() {
        return [
            'name' => 'Friends',
            'description' => 'Friends list, list of friend requests'
        ];
    }

    public function defineProperties() {
        return [
            'type' => [
                'title' => 'Type',
                'type' => 'dropdown',
                'default' => 'list',
                'placeholder' => 'Select type',
            ],
            'maxItems' => [
                'title' => 'Max items',
                'description' => 'Max items to show in a list. 0=unlimited',
                'default' => 5,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Items property can contain only numeric symbols'
            ],
            'paramCode' => [
                'title' => 'User ID URL parameter',
                'description' => 'Specifies a user ID to generate a list for. blank=logged in user',
                'type' => 'string',
                'default' => ''
            ],
            'profilePage' => [
                'title' => 'Profile Page',
                'description' => 'The page to redirect to for user profiles.',
                'type' => 'dropdown',
                'default' => 'user/profile'
            ]
        ];
    }

    /**
     * Used for properties dropdown menu
     * @return array
     */
    public function getTypeOptions() {
        return ['list' => 'Friends List', 'requests' => 'Friend Requests'];
    }

    /**
     * Used for properties dropdown menu
     * @return mixed
     */
    public function getProfilePageOptions() {
        $user = new User();
        return $user->getProfilePageOptions();
    }

    /**
     * Injects assets
     */
    public function onRun() {
        Plugin::injectAssets($this);
    }

    /**
     * Returns the list/component type
     */
    public function type() {
        return $this->property('type');
    }

    /**
     * Returns a variable to the page which lists a users friends.
     *
     */
    public function friendsList() {
        if (!Settings::get('enable_friends', true))
            return null;

        $limit = $this->property('maxItems');

        $userId = null;

        $urlType = Settings::get('url_type');
        $urlParam = Settings::get('url_param');
        $user = CoreUser::where($urlType, $this->param($urlParam))->first();
        $code = $user->id;

        if ($code != '')
            $userId = $this->param($code);

        if (empty($userId))
            $userId = UserUtil::getLoggedInUser();

        if (isset($userId))
            $userId = $userId->id;

        return FriendsManager::listFriends($limit, $userId);
    }

    /**
     * AJAX call to delete a friend
     */
    public function onDelete() {
        $userid = post('id');

        if ($userid != null) {
            FriendsManager::deleteFriend($userid);
            return [
                '#list-wrap' => $this->renderPartial('friends::friends_list.htm', ['friendRequests' => $this->friendRequests()])
            ];
        }
    }

    /**
     * AJAX call to block a user
     */
    public function onBlock() {
        $userid = post('id');

        if ($userid != null) {
            FriendsManager::blockFriend($userid);
        }
    }

    /**
     * AJAX handler for redirecting a user to a profile page.
     * @return mixed
     */
    public function onVisitProfile() {
        $user = new User();
        return $user->onVisitProfile($this->property('profilePage'));
    }

    /**
     * Returns a list of users who have requested you to be their friend
     *
     * @return \Illuminate\Support\Collection
     */
    public function friendRequests() {
        $limit = $this->property('maxItems');

        return FriendsManager::listReceivedFriendRequests($limit);
    }

    /**
     * AJAX call when a button is clicked to accept a friend request
     */
    public function onAccept() {
        $userid = post('id');

        if ($userid != null) {
            FriendsManager::acceptRequest($userid);
            //Refresh the page so you know it did something
            return [
                '#request-wrap' => $this->renderPartial('friends::friend_requests.htm', ['friendRequests' => $this->friendRequests()])
            ];
        }
    }

    /**
     * AJAX handler to decline friend requests
     */
    public function onDecline() {
        $userid = post('id');

        if ($userid != null) {
            FriendsManager::declineRequest($userid);
            //Refresh the page so you know it did something
            return [
                '#request-wrap' => $this->renderPartial('friends::friend_requests.htm', ['friendRequests' => $this->friendRequests()])
            ];
        }
    }

    /**
     * AJAX handler for sending a friend request
     */
    public function onRequest() {
        $userId = post('id');
        FriendsManager::sendFriendRequest($userId);
        return [
            '#list-wrap' => $this->renderPartial('friends::friends_list.htm', ['friendRequests' => $this->friendRequests()])
        ];
    }

}
