<?php namespace Clake\Userextended\Components;

use Clake\UserExtended\Classes\CommentManager;
use Clake\UserExtended\Classes\FriendsManager;
use Clake\UserExtended\Classes\UserRoleManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\Comments;
use Cms\Classes\ComponentBase;

/**
 * TODO: Rename the component and separate out some of the modules.
 *      TODO: Commenting should be its own set as should friending
 */

/**
 * Class UserUI
 * @package Clake\Userextended\Components
 */
class UserUI extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'User UI',
            'description' => 'Provides generic interface implementations'
        ];
    }

    public function defineProperties()
    {
        return [
            'user' => [
                'title'             => 'User',
                'description'       => 'The user id for the user',
                'default'           => ':user',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Items property can contain only numeric symbols'
            ]
        ];
    }

    /**
     * Provides user information to the page
     * @return mixed
     */
    public function user()
    {
        $userid = $this->property('user');

        return UserUtil::getRainlabUser($userid);
    }

    /**
     * Returns whether or not the user is our friend and thus
     * whether or not the page should be partially restricted
     * @return bool
     */
    public function unrestricted()
    {
        $userid = $this->property('user');

        if(!UserUtil::getLoggedInUser())
            return null;

        return (FriendsManager::isFriend($userid)) || (UserUtil::getLoggedInUser()->id == $userid);
    }

    /**
     * AJAX call for when someone wants to send a friend request
     */
    public function onFriendUser()
    {
        $userid = $this->property('user');

        FriendsManager::sendFriendRequest($userid);
    }

    /**
     * Returns a collection of comments for a users profile
     * @return mixed
     */
    public function comments()
    {
        $userid = $this->property('user');

        return UserUtil::getUser($userid)->comments()->orderby('updated_at', 'desc')->get();
    }

    /**
     * AJAX handler for when someone leaves a comment on a profile
     * @return array
     */
    public function onComment()
    {
        $userid = $this->property('user');
        $content = post('comment');

        CommentManager::createComment($userid, $content);

        return $this->renderComments($this->comments());
    }

    public function onDeleteComment()
    {
        $userid = $this->property('user');
        $commentid = post('commentid');

        CommentManager::deleteComment($commentid);

        return $this->renderComments($this->comments());
    }

    /**
     * Used to dynamically update the comment section when a user leaves a new comment
     * @param $comments
     * @return array
     */
    private function renderComments($comments)
    {
        $content = $this->renderPartial('userui::comments.htm', ['comments' => $comments]);
        return ['#comment_section' => $content];
    }

    public function roles()
    {
        //$roles = UserRoleManager::currentUser()->all()->promote('developer');


        return json_encode(UserRoleManager::currentUser()->all()->get());
    }

}