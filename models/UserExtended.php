<?php namespace Clake\Userextended\Models;

use Model;
use RainLab\User\Models\User;

use Clake\UserExtended\Traits\Timezonable;

/**
 * UserExtended Model
 */
class UserExtended extends User
{

    protected $timezonable = [

        'created_at',
        'updated_at'

    ];

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

        $belongsTo = $this->belongsTo;
        $belongsTo['timezone'] = ['Clake\UserExtended\Models\Timezone', 'key' => 'timezone_id'];
        $this->belongsTo = $belongsTo;

        $belongsToMany = $this->belongsToMany;
        $belongsToMany['roles'] = ['Clake\Userextended\Models\Roles', 'table' => 'users_groups', 'key' => 'user_id', 'otherKey' => 'role_id'];
        $this->belongsToMany = $belongsToMany;

        parent::__construct();
    }

}