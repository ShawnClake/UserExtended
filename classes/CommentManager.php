<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Comments;

/**
 * TODO: Edit a comment
 * TODO: Get comments to follow SRP
 * TODO: Verify logged in user should have access to the comment
 */

/**
 * Class CommentManager
 * @package Clake\UserExtended\Classes
 */
class CommentManager
{

    /**
     * Creates a comment for a user
     *
     * @param $userid
     * @param $content
     */
    public static function createComment($userid, $content)
    {
        $user = UserUtil::getUser($userid);

        $author = UserUtil::getLoggedInUser();

        if(is_null($author) || is_null($user))
            return false;

        $comment = new Comments();

        $comment->user = $user;
        $comment->author = $author;
        $comment->content = $content;

        $comment->save();
    }

    /**
     * Deletes a comment specified by a comment ID
     * @param $commentid
     */
    public static function deleteComment($commentid)
    {

        if(self::canDeleteComment($commentid))
        {
            $comment = Comments::where('id', $commentid)->first();
            $comment->delete();
        }

    }

    /**
     *
     */
    public static function canCreateComment()
    {

    }

    /**
     * @param $commentid
     * @return bool
     */
    public static function canDeleteComment($commentid)
    {
        $loggedInUser = UserUtil::getLoggedInUser();

        if(is_null($loggedInUser))
            return false;

        $accessible = false;

        if(Comments::where('id', $commentid)->where('user_id', $loggedInUser->id)->count() == 1)
            $accessible = true;

        if(Comments::where('id', $commentid)->where('author_id', $loggedInUser->id)->count() == 1)
            $accessible = true;

        return $accessible;
    }

    /**
     * @param $commentid
     * @return bool
     */
    public static function canEditComment($commentid)
    {
        $loggedInUser = UserUtil::getLoggedInUser();

        if(is_null($loggedInUser))
            return false;

        if(Comments::where('id', $commentid)->where('author_id', $loggedInUser->id)->count() == 1)
            return true;

        return false;
    }

    /**
     * @param $commentid
     * @param $content
     */
    public static function editComment($commentid, $content)
    {
        if(self::canEditComment($commentid))
        {
            $comment = Comments::where('id', $commentid)->first();
            $comment->content = $content;
            $comment->save();
        }
    }

}