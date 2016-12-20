<?php namespace Clake\UserExtended\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use RainLab\User\Models\UserGroup;

/**
 * @depreciated
 * TODO: Remove this controller
 */

/**
 * User Groups Back-end Controller
 */
class GroupsExtended extends Controller
{
    public $implement = [
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController',
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.RelationController',
    ];

    public $reorderConfig = 'config_reorder.yaml';
    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    //public $requiredPermissions = ['rainlab.users.access_groups'];

    public function __construct()
    {
        parent::__construct();

        //BackendMenu::setContext('RainLab.User', 'user', 'usergroups');
        BackendMenu::setContext('RainLab.User', 'user', 'users');
    }
}