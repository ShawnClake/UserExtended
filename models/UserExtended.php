<?php namespace Clake\Userextended\Models;

use Model;
use RainLab\User\Models\User;

/**
 * UserExtended Model
 */
class UserExtended extends User
{

    /**
     * Used to manually add relations for the user table
     * UserExtended constructor.
     */
    public function __construct()
    {

        $hasMany = $this->hasMany;
        $hasMany['comments'] = ['Clake\Userextended\Models\Comments', 'key'=>'user_id'];
        $hasMany['authored_comments'] = ['Clake\Userextended\Models\Comments', 'key'=>'author_id'];
        $this->hasMany = $hasMany;

        parent::__construct();
    }

}