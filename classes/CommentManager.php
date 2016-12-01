<?php

namespace Clake\UserExtended\Classes;

use Clake\Userextended\Models\Comments;



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
        $loggedInUser = UserUtil::getLoggedInUser();

        $accessible = false;

        if(Comments::where('id', $commentid)->where('user_id', $loggedInUser->id)->count() == 1)
            $accessible = true;

        if(Comments::where('id', $commentid)->where('author_id', $loggedInUser->id)->count() == 1)
            $accessible = true;

        if($accessible)
        {
            $comment = Comments::where('id', $commentid)->first();
            $comment->delete();
        }

    }

}