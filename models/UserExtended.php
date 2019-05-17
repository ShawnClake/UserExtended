<?php

namespace Clake\Userextended\Models;

use Model;
use RainLab\User\Models\User;
use Clake\UserExtended\Traits\Timezonable;
use Clake\UserExtended\Traits\Searchable;

/**
 * User Extended by Shawn Clake
 * Class UserExtended
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Models
 */
class UserExtended extends User {

    use Timezonable;

    use Searchable;

    /**
     * @var array
     */
    protected $timezonable = [
    ];

    /**
     * @var array
     */
    protected $searchable = [
        'email',
        'name',
        'surname',
        'username'
    ];

    /**
     * Used to manually add relations for the user table
     * UserExtended constructor.
     */
    public function __construct() {
        $hasMany = $this->hasMany;
        $hasMany['comments'] = ['Clake\Userextended\Models\Comment', 'key' => 'user_id', 'softDelete' => true, 'delete' => true];
        $hasMany['authored_comments'] = ['Clake\Userextended\Models\Comment', 'key' => 'author_id', 'softDelete' => true, 'delete' => true];
        $hasMany['sent_relations'] = ['Clake\Userextended\Models\Friend', 'key' => 'user_that_sent_request', 'softDelete' => true, 'delete' => true];
        $hasMany['received_relations'] = ['Clake\Userextended\Models\Friend', 'key' => 'user_that_accepted_request', 'softDelete' => true, 'delete' => true];
        $hasMany['integrated_user'] = ['Clake\Userextended\Models\IntegratedUser', 'key' => 'user_id', 'softDelete' => true, 'delete' => true];
        $this->hasMany = $hasMany;

        $belongsTo = $this->belongsTo;
        $belongsTo['timezone'] = ['Clake\UserExtended\Models\Timezone', 'key' => 'timezone_id'];
        $this->belongsTo = $belongsTo;

        $belongsToMany = $this->belongsToMany;
        $belongsToMany['roles'] = ['Clake\Userextended\Models\Role', 'table' => 'users_groups', 'key' => 'user_id', 'otherKey' => 'role_id'];
        $belongsToMany['groups'] = ['Clake\Userextended\Models\GroupsExtended', 'table' => 'users_groups', 'key' => 'user_id', 'otherKey' => 'user_group_id', 'softDelete' => true, 'delete' => true];
        $this->belongsToMany = $belongsToMany;

        $json = $this->jsonable;
        array_push($json, 'settings');
        $this->jsonable = $json;

        parent::__construct();
    }

}
