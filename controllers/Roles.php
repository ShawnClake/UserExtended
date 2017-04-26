<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\Feedback\GroupFeedback;
use Clake\UserExtended\Classes\Feedback\RoleFeedback;
use Clake\UserExtended\Classes\GroupManager;
use Clake\UserExtended\Classes\RoleManager;
use Clake\UserExtended\Classes\UserGroupManager;
use Clake\UserExtended\Classes\UserRoleManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\GroupsExtended;
use Clake\Userextended\Models\Role;
use Clake\Userextended\Models\UsersGroups;
use Redirect;
use Session;

/**
 * User Extended by Shawn Clake
 * Class Roles
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Controllers
 */
class Roles extends Controller
{

    /**
     * RoleManager render types
     */
    const UE_CREATE_GROUP_FORM = 'create_group_form'; // _create_group_form
    const UE_CREATE_ROLE_FORM = 'create_role_form'; // _create_role_form

    const UE_UPDATE_GROUP_FORM = 'update_group_form'; // _update_group_form
    const UE_UPDATE_ROLE_FORM = 'update_role_form'; // _update_role_form

    const UE_LIST_GROUP_BUTTONS = 'list_group_buttons'; // _list_groups
    const UE_LIST_ROLES_TABLE = 'list_roles_table'; // _list_roles
    const UE_LIST_ROLES_TABLE_UNASSIGNED = 'list_roles_table_unassigned'; // _list_unassigned_roles

    const UE_MANAGE_CREATION_TOOLBAR = 'manage_creation_toolbar'; // _create_toolbar
    const UE_MANAGE_GROUP_TOOLBAR = 'manage_group_toolbar'; // _manage_group_toolbar
    const UE_MANAGE_ROLE_TOOLBAR = 'manage_role_toolbar'; // _management_role_toolbar
    const UE_MANAGE_OVERALL_TOOLBAR = 'manage_overall_toolbar'; // _management_toolbar
    const UE_MANAGE_ROLE_UI = 'manage_role_ui'; // _manage_role
    const UE_MANAGE_USERS_UI = 'manage_users_ui'; // _list_unassigned_user_in_group

    /**
     * How many results can be shown in a table
     */
    const RESULT_LIM = 8;

    /**
     * A queue of all renders that need to be preformed
     * @var array
     */
    public static $queue = [];

    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();

        // Setting this context so that our sidebar menu works
        BackendMenu::setContext('RainLab.User', 'user', 'roles');
		
        $this->addJs('/plugins/clake/userextended/assets/js/libs/interact.min.js');
        $this->addJs('/plugins/clake/userextended/assets/js/general.js');
        $this->addJs('/plugins/clake/userextended/assets/js/backend.js');

        //Add CSS for some backend menus
        $this->addCss('/plugins/clake/userextended/assets/css/general.css');
        $this->addCss('/plugins/clake/userextended/assets/css/backend.css');
    }



    /**
     * Action used for managing roles such as: their order, some stats, and their properties
     * TODO: This needs a major cleanup and refactor
     */
    public function manage()
    {
        $this->pageTitle = "Manage Roles";

        $this->vars['groups'] = GroupManager::allGroups()->getGroups();

        //$this->flushSession();

        $this->vars['selectedGroup'] = null;
        $this->vars['currentGroupCode'] = null;
        $this->vars['unassignedRoles'] = null;
        $this->vars['unassignedRoleCount'] = null;
        $this->vars['unassignedUsers'] = null;

        if(GroupManager::allGroups()->countGroups() <= 0)
            return;


        if($this->getCurrentGroup() === false || $this->getCurrentGroup() === null)
            $this->setCurrentGroup(GroupManager::allGroups()->getGroups()->first()->code);

        $this->vars['selectedGroup'] = GroupManager::findGroup($this->getCurrentGroup());
        $this->vars['currentGroupCode'] = $this->getCurrentGroup();

        $rolesUnsorted = RoleManager::with($this->getCurrentGroup());
        $roles = $rolesUnsorted->sort()->getRoles();

        $unassignedRoles = RoleManager::getUnassignedRoles();
        $this->vars['unassignedRoles'] = $unassignedRoles;
        $this->vars['unassignedRoleCount'] = $unassignedRoles->count();

        $unassignedUsers = UsersGroups::byUsersWithoutRole($this->vars['selectedGroup']->code)->get();
        $this->vars['unassignedUsers'] = $unassignedUsers;

        if(!isset($roles))
            return;

        if($this->getCurrentPage('list_roles') === false || $this->getCurrentPage('list_roles') === null)
            $this->setCurrentPage('list_roles', 1);
        $total = ceil((float)$roles->count() / (float)self::RESULT_LIM);

        $this->vars['groupRoles'] = ['roles' => $roles->slice(($this->getCurrentPage('list_roles') - 1) * self::RESULT_LIM, self::RESULT_LIM), 'pagination' => ['page' => $this->getCurrentPage('list_roles'), 'total' => $total]];

        if(($this->getCurrentRole() === false || $this->getCurrentRole() === null) && count($roles) > 0)
            $this->setCurrentRole($roles->first()->code);

        if(count($roles) > 0)
            $this->vars['role'] = RoleManager::findRole($this->getCurrentRole());
    }

    /**
     * AJAX handler used when a user clicks on a different group
     * @return array
     */
    public function onSelectGroup()
    {
        $groupCode = post('groupCode');
        $group = GroupManager::findGroup($groupCode);

        if(empty($group) || !isset($group))
            return [];

        $this->setCurrentGroup($group->code);

        $this->flushCurrentPages();

        $roles = RoleManager::with($groupCode)->sort()->getRoles();

        if($roles->count() > 0)
        {
            $this->setCurrentRole($roles[0]->code);
            $this->queue([
                self::UE_MANAGE_ROLE_UI,
                self::UE_LIST_ROLES_TABLE,
                self::UE_MANAGE_ROLE_TOOLBAR
            ]);
        }
        else
        {
            $this->setCurrentRole(null);
            $this->queueBlank([
                self::UE_MANAGE_ROLE_UI,
                self::UE_MANAGE_ROLE_TOOLBAR,
            ]);
            $this->queue([self::UE_LIST_ROLES_TABLE]);
        }

        $this->queue([
            self::UE_MANAGE_OVERALL_TOOLBAR,
            self::UE_LIST_GROUP_BUTTONS,
            self::UE_LIST_ROLES_TABLE_UNASSIGNED,
            self::UE_MANAGE_USERS_UI,
            self::UE_MANAGE_GROUP_TOOLBAR,
        ]);

        return $this->render();
    }

    /**
     * AJAX handler called when trying to move a role higher in the hierarchy
     * @return array
     */
    public function onMoveRoleUp()
    {
        $groupCode = post('groupCode');
        $roleSortOrder = post('order');
        RoleManager::with($groupCode)->sortUp($roleSortOrder);

        $this->queue([self::UE_LIST_ROLES_TABLE]);
        return $this->render();
    }

    /**
     * AJAX handler called when trying to move a role lower in the hierarchy
     * @return array
     */
    public function onMoveRoleDown()
    {
        $groupCode = post('groupCode');
        $roleSortOrder = post('order');
        RoleManager::with($groupCode)->sortDown($roleSortOrder);

        $this->queue([self::UE_LIST_ROLES_TABLE]);
        return $this->render();
    }

    /**
     * AJAX handler called when clicking on a different role to manage it
     * @return array
     */
    public function onManageRole()
    {
        $roleCode = post('roleCode');
        $this->setCurrentRole($roleCode);

        $this->queue([
            self::UE_MANAGE_ROLE_UI,
            self::UE_MANAGE_ROLE_TOOLBAR,
            self::UE_MANAGE_USERS_UI,
        ]);
        return $this->render();
    }

    /**
     * AJAX handler called when hitting the edit role button in the role manager.. Used to edit the role.
     * @return mixed
     */
    public function onOpenRoleEditor()
    {
        $roleCode = post('roleCode');
        $this->setCurrentRole($roleCode);
        $this->queue([
            self::UE_UPDATE_ROLE_FORM,
        ]);
        return $this->render()[0];
    }

    /**
     * AJAX handler called to save the role after the user clicks save in the role editor window
     * @return array
     */
    public function onEditRole()
    {
        $groupCode = post('groupCode');
        $roleCode = post('roleCode');
        $name = post('name');
        $code = post('code');
        $description = post('description');

        $feedback = RoleManager::updateRole($roleCode, null, $name, $description, $code, null, true);

        $roles = RoleManager::with($groupCode)->sort()->getRoles();

        $uiFeedback = RoleFeedback::with($feedback)->flash()->display('#feedback_role_save');

        if(!($feedback === false || $feedback->fails())) {
            $this->setCurrentRole($code);

            if($roles->count() > 0) {
                $this->queue([
                    self::UE_MANAGE_ROLE_UI,
                    self::UE_MANAGE_ROLE_TOOLBAR
                ]);
            } else {
                $this->queueBlank([
                    self::UE_MANAGE_ROLE_UI,
                    self::UE_MANAGE_ROLE_TOOLBAR
                ]);
            }

            $this->queue([self::UE_LIST_ROLES_TABLE]);
        }

        return array_merge($this->render(), $uiFeedback);
    }

    /**
     * AJAX handler for removing a role
     * @return array
     */
    public function onRemoveRole()
    {
        $groupCode = post('groupCode');
        $roleCode = post('roleCode');

        if(!isset($groupCode))
        {
            $role = RoleManager::findRole($roleCode);

            if(!isset($role))
                return;

            $role->delete();

            $this->queue([
                self::UE_LIST_ROLES_TABLE_UNASSIGNED,
            ]);

            return $this->render();
        }  else {

            $role = RoleManager::with($groupCode)->getRole($roleCode);

            if(!isset($role))
                return;

            $role->delete();

            if(RoleManager::with($groupCode)->sort()->getRoles()->count() > 0 && $roleCode == $this->getCurrentRole())
                $this->setCurrentRole(RoleManager::with($groupCode)->getRoles()->first()->code);

            $roles = RoleManager::with($groupCode)->sort()->getRoles();
            if($roles->count() > 0)
            {
                $this->queue([
                    self::UE_MANAGE_ROLE_UI,
                    self::UE_MANAGE_ROLE_TOOLBAR,
                ]);
            }
            else
            {
                $this->queueBlank([
                    self::UE_MANAGE_ROLE_UI,
                    self::UE_MANAGE_ROLE_TOOLBAR,
                ]);
            }

            $this->queue([self::UE_LIST_ROLES_TABLE]);

            return array_merge($this->render());
        }
    }

    /**
     * AJAX handler for opening a form for creating a new role
     * @return mixed
     */
    public function onOpenRoleCreator()
    {
        $this->queue([self::UE_CREATE_ROLE_FORM]);
        return $this->render()[0];
    }

    /**
     * AJAX handler for creating a new role when clicking create/save on a create role form.
     * @return array
     */
    public function onCreateRole()
    {
        $name = post('name');
        $code = post('code');
        $description = post('description');

        $feedback = RoleManager::createRole($name, $description, $code, -1);

        $uiFeedback = RoleFeedback::with($feedback)->flash()->display('#feedback_role_save');

        $this->queue([
            self::UE_LIST_ROLES_TABLE_UNASSIGNED,
        ]);
        return array_merge($this->render(), $uiFeedback);
    }

    /**
     * Removes a user from a role. This does not remove them from the group.
     * It only sets their role_id to 0 in UsersGroups tbl
     * @return array
     */
    public function onUnassignUser()
    {
        $userId = post('userId');
        $roleId = post('roleId');

        $relation = UsersGroups::where('user_id', $userId)->where('role_id', $roleId)->first();
        $relation->role_id = 0;
        $relation->save();

        $this->queue([
            self::UE_MANAGE_ROLE_UI,
            self::UE_MANAGE_USERS_UI,
        ]);

        return $this->render();
    }

    /**
     * Promotes a user
     * @return array
     */
    public function onPromoteUser()
    {
        $userId = post('userId');
        $roleCode = post('roleCode');

        $role = \Clake\Userextended\Models\Role::where('code', $roleCode)->first();

        UserRoleManager::with(UserUtil::getUser($userId))->allRoles()->promote($role->group->code);

        $this->queue([
            self::UE_MANAGE_ROLE_UI,
        ]);
        return $this->render();
    }

    /**
     * Demotes a user
     * @return array
     */
    public function onDemoteUser()
    {
        $userId = post('userId');
        $roleCode = post('roleCode');

        $role = \Clake\Userextended\Models\Role::where('code', $roleCode)->first();

        UserRoleManager::with(UserUtil::getUser($userId))->allRoles()->demote($role->group->code);

        $this->queue([
            self::UE_MANAGE_ROLE_UI,
        ]);
        return $this->render();
    }

    /**
     * Handles assigning a role
     */
    public function onAssignRole()
    {
        $roleCode = post('roleCode');
        $groupCode = $this->getCurrentGroup();
        if($groupCode === false)
            return false;

        $sortOrder = RoleManager::with($groupCode)->countRoles() + 1;
        $groupId = GroupManager::findGroup($groupCode)->id;

        RoleManager::updateRole($roleCode, $sortOrder, null, null, null, $groupId, true);

        $roles = RoleManager::with($groupCode)->sort()->getRoles();

        $this->setCurrentRole($roleCode);

        if($roles->count() > 0)
        {
            $this->queue([
                self::UE_MANAGE_ROLE_UI,
                self::UE_MANAGE_ROLE_TOOLBAR,
            ]);
        }
        else
        {
            $this->queueBlank([
                self::UE_MANAGE_ROLE_UI,
                self::UE_MANAGE_ROLE_TOOLBAR,
            ]);
        }

        $this->queue([
            self::UE_LIST_ROLES_TABLE,
            self::UE_LIST_ROLES_TABLE_UNASSIGNED,
        ]);

        return $this->render();
    }

    /**
     * Handles unassigning a role
     * @return array
     */
    public function onUnassignRole()
    {
        $roleCode = post('roleCode');
        $groupCode = post('groupCode');

        $rows = UsersGroups::byRole($roleCode)->get();
        foreach($rows as $relation)
        {
            $relation->role_id = 0;
            $relation->save();
        }

        RoleManager::updateRole($roleCode, 1, null, null, null, 0, true);

        RoleManager::with($groupCode)->fixRoleSort();

        $roles = RoleManager::with($groupCode)->sort()->getRoles();
        if($roles->count() > 0 && $roleCode == $this->getCurrentRole())
            $this->setCurrentRole($roles[0]->code);


        if($roles->count() > 0)
        {
            $this->queue([
                self::UE_MANAGE_ROLE_UI,
                self::UE_MANAGE_ROLE_TOOLBAR,
            ]);
        }
        else
        {
            $this->queueBlank([
                self::UE_MANAGE_ROLE_UI,
                self::UE_MANAGE_ROLE_TOOLBAR,
            ]);
        }

        $this->queue([
            self::UE_LIST_ROLES_TABLE,
            self::UE_LIST_ROLES_TABLE_UNASSIGNED,
            self::UE_MANAGE_USERS_UI
        ]);

        return $this->render();
    }

    /**
     * AJAX handler for deleting a group
     * @return mixed
     */
    public function onDeleteGroup()
    {
        $groupCode = $this->getCurrentGroup();
        if($groupCode === false)
            return false;

        GroupManager::deleteGroup($groupCode);

        $groups = GroupManager::allGroups()->getGroups();

        if($groups->count() <= 0)
        {
            $this->setCurrentRole(null);
            $this->setCurrentGroup(null);

            $this->queue([
                self::UE_LIST_GROUP_BUTTONS,
            ]);

            $this->queueBlank([
                self::UE_MANAGE_ROLE_UI,
                self::UE_MANAGE_ROLE_TOOLBAR,
                self::UE_LIST_ROLES_TABLE,
                self::UE_MANAGE_USERS_UI,
            ]);
        } else {
            $this->setCurrentGroup($groups->first()->code);
            $this->setCurrentRole(null);

            $this->queue([
                self::UE_LIST_GROUP_BUTTONS,
                self::UE_MANAGE_USERS_UI,
                self::UE_LIST_ROLES_TABLE,
                self::UE_MANAGE_ROLE_UI,
            ]);
        }

        return $this->render();
    }

    /**
     * AJAX handler for opening the create a group modal
     * @return mixed
     */
    public function onOpenGroupCreator()
    {
        $this->queue([self::UE_CREATE_GROUP_FORM]);
        return $this->render()[0];
    }

    /**
     * AJAX handler for creating a group when clicking 'create'
     * @return mixed
     */
    public function onCreateGroup()
    {
        $name = post('name');
        $code = post('code');
        $description = post('description');

        $feedback = GroupManager::createGroup($name, $description, $code);

        $uiFeedback = GroupFeedback::with($feedback)->flash()->display('#feedback_group_save');

        $this->setCurrentGroup($code);
        $this->setCurrentRole(null);

        $this->queue([
            self::UE_LIST_GROUP_BUTTONS,
            self::UE_MANAGE_USERS_UI,
            self::UE_LIST_ROLES_TABLE,
        ]);

        $this->queueBlank([
            self::UE_MANAGE_ROLE_UI,
            self::UE_MANAGE_ROLE_TOOLBAR,
        ]);

        return array_merge($this->render(), $uiFeedback);
    }

    /**
     * AJAX handler to open the update group modal
     * @return mixed
     */
    public function onOpenGroupEditor()
    {
        $groupCode = post('groupCode');
        $this->setCurrentGroup($groupCode);
        $this->queue([
            self::UE_UPDATE_GROUP_FORM,
        ]);
        return $this->render()[0];
    }

    /**
     * AJAX handler for saving the update group modal
     * @return mixed
     */
    public function onEditGroup()
    {
        $groupCode = post('groupCode');
        $name = post('name');
        $code = post('code');
        $description = post('description');

        $feedback = GroupManager::updateGroup($groupCode, $name, $description, $code);

        $uiFeedback = GroupFeedback::with($feedback)->flash()->display('#feedback_group_save');
        
        $this->setCurrentGroup($code);

        $this->queue([
            self::UE_LIST_GROUP_BUTTONS,
            self::UE_MANAGE_USERS_UI,
        ]);

        return array_merge($this->render(), $uiFeedback);
    }

    /**
     * AJAX handler for assigning groups already in a group to a role
     * @return array
     */
    public function onAssignUser()
    {
        $roleCode = post('roleCode');
        $userId = post('userId');

       UserRoleManager::with(UserUtil::getUser($userId))->addRole($roleCode);

       $this->queue([
           self::UE_MANAGE_USERS_UI,
           self::UE_MANAGE_ROLE_UI,
       ]);

        return $this->render();
    }

    /**
     * AJAX handler for removing a group from a user
     * @return array|false
     */
    public function onRemoveUserFromGroup()
    {
        $groupCode = $this->getCurrentGroup();
        if($groupCode === false)
            return false;

        $userId = post('userId');

        UserGroupManager::with(UserUtil::getUser($userId))->removeGroup($groupCode);

        $this->queue([self::UE_MANAGE_USERS_UI,]);

        return $this->render();
    }

    /**
     * AJAX handler for redirecting when clicking on a users name
     */
    public function onManageUser()
    {
        $userid = post('userId');
        if(!isset($userid))
            return false;

        return Redirect::to(Backend::url('rainlab/user/users/preview/' . $userid));
    }

    /**
     * Page left for table pagination
     * @return array|bool
     */
    public function onPageLeft()
    {
        $groupCode = $this->getCurrentGroup();
        if($groupCode === false)
            return false;

        $tbl = post('table');

        $page = $this->getCurrentPage($tbl);

        if($page === false)
            $page = 1;
        else
            $page--;

        if($page < 1 )
            $page = 1;

        if($this->getCurrentPage($tbl) == $page)
            return false;

        $this->setCurrentPage($tbl, $page);

        $this->queue([self::UE_LIST_ROLES_TABLE]);

        return $this->render();
    }

    /**
     * Page right for table pagination
     * @return array|bool
     */
    public function onPageRight()
    {
        $groupCode = $this->getCurrentGroup();
        if($groupCode === false)
            return false;

        $tbl = post('table');

        $page = $this->getCurrentPage($tbl);
        if($page === false)
            $page = 2;
        else
            $page++;

        $roleCount = RoleManager::with($this->getCurrentGroup())->countRoles();
        $maxPages = ceil((float)$roleCount / (float)self::RESULT_LIM);

        if($page < 1 )
            $page = 1;

        if($page > $maxPages)
            $page = $maxPages;

        if($this->getCurrentPage($tbl) == $page)
            return false;

        $this->setCurrentPage($tbl, $page);

        $this->queue([self::UE_LIST_ROLES_TABLE]);

        return $this->render();
    }

    /**
     * Choose a page for table pagination
     * @return array|bool
     */
    public function onPageChoose()
    {
        $groupCode = $this->getCurrentGroup();
        if($groupCode === false)
            return false;

        $tbl = post('table');
        $page = post('page');

        $roleCount = RoleManager::with($this->getCurrentGroup())->countRoles();
        $maxPages = ceil((float)$roleCount / (float)self::RESULT_LIM);

        if($page < 1 )
            return false;

        if($page > $maxPages)
            return false;

        if($this->getCurrentPage($tbl) == $page)
            return false;

        $this->setCurrentPage($tbl, $page);

        $this->queue([self::UE_LIST_ROLES_TABLE]);

        return $this->render();
    }

    /**
     * Resets the application to default roles and groups
     */
    public function onResetToDefault()
    {
        $this->flushSession();

        Role::truncate();
        GroupsExtended::truncate();
        UsersGroups::truncate();

        GroupManager::seedBasicUserGroups();

        return Redirect::to(Backend::url('clake/userextended/roles/manage'));
    }


    /**
     * Queues the partials for render.
     * This function requires an array of partials const's to signify which partials to render.
     * Any session data should be changed prior to calling Queue.
     * This does not also preform the render.
     * @param array $to_queue
     * @return bool
     */
    protected function queue(array $to_queue)
    {
        if(empty($to_queue))
            return false;

        $prefix = 'queueUe';

        $success = true;

        foreach($to_queue as $queueType)
        {
            $function = $prefix . studly_case($queueType);
            if(!$this->$function())
                $success = false;
        }

        return $success;
    }

    /**
     * Queues a div to be emptied.
     * @param array $to_queue
     * @return bool
     */
    protected function queueBlank(array $to_queue)
    {
        if(empty($to_queue))
            return false;

        foreach($to_queue as $queueType)
        {
            self::$queue[] = [$queueType, [], 'blank' => true];
        }

        return true;
    }

    /**
     * Renders each of the passed const's and returns a merged array of them
     * $to_render is an array containing arrays of the following format [UE_CREATE_ROLE_FORM, ['group' => groupCode, 'role' => roleCode]]
     * @param array $to_render
     * @return array
     */
    protected function render(array $to_render = [])
    {
        if(empty($to_render))
            $to_render = self::$queue;

        if(empty($to_render))
            return [];

        $renders = [];

        foreach($to_render as $renderType)
        {
            $partialName = $renderType[0];
            $divId = '#' . $partialName;
            $vars = $renderType[1];

            if(isset($renderType['blank']) && $renderType['blank'] === true)
            {
                $renders = array_merge($renders, [$divId => '']);
                continue;
            }

            $partial = $this->makePartial($partialName, $vars);

            if(isset($renderType['override_key']) && $renderType['override_key'] === true)
            {
                $renders = array_merge($renders, [$partial]);
            } else {
                $renders = array_merge($renders, [$divId => $partial]);
            }
        }

        $this->flushQueue();

        return $renders;
    }

    /**
     * Flushes the queue
     * Remove all renders in queue
     */
    private function flushQueue()
    {
        self::$queue = [];
    }

    /**
     * Flushes the Session
     * Removes session keys used by RoleManager
     */
    private function flushSession()
    {
        if(Session::has('ue.backend.role_manager.current_group'))
            Session::forget('ue.backend.role_manager.current_group');

        if(Session::has('ue.backend.role_manager.current_role'))
            Session::forget('ue.backend.role_manager.current_role');
    }

    /**
     * Removes the pagination session data
     */
    private function flushCurrentPages()
    {
        if(Session::has('ue.backend.role_manager.current_page.list_roles'))
            Session::forget('ue.backend.role_manager.current_page.list_roles');
    }

    /**
     * Retrieves the current group from the session
     * @return bool|string
     */
    private function getCurrentGroup()
    {
        $group = null;
        if(Session::has('ue.backend.role_manager.current_group'))
            $group = Session::get('ue.backend.role_manager.current_group', null);
        if(!empty($group))
            return $group;
        return false;
    }

    /**
     * Sets the current group into the session
     * @param $groupCode
     * @return bool
     */
    private function setCurrentGroup($groupCode)
    {
        if(Session::put('ue.backend.role_manager.current_group', $groupCode))
            return true;
        return false;
    }

    /**
     * Gets the current role from the session
     * @return bool|string
     */
    private function getCurrentRole()
    {
        $role = null;
        if(Session::has('ue.backend.role_manager.current_role'))
            $role = Session::get('ue.backend.role_manager.current_role', null);
        if(!empty($role))
            return $role;
        return false;
    }

    /**
     * Sets the current role into the session
     * @param $roleCode
     * @return bool
     */
    private function setCurrentRole($roleCode)
    {
        if(Session::put('ue.backend.role_manager.current_role', $roleCode))
            return true;
        return false;
    }

    /**
     * Gets the current page from session for pagination
     * @param $tbl
     * @return bool|null
     */
    private function getCurrentPage($tbl)
    {
        $page = null;
        if(Session::has('ue.backend.role_manager.current_page.' . $tbl))
            $page = Session::get('ue.backend.role_manager.current_page.' . $tbl, null);
        if(!empty($page))
            return $page;
        return false;
    }

    /**
     * Sets the current page for a table into session for pagination
     * @param $tbl
     * @param $page
     * @return bool
     */
    private function setCurrentPage($tbl, $page)
    {
        if($page < 1)
            $page = 1;
        if(Session::put('ue.backend.role_manager.current_page.' . $tbl, $page))
            return true;
        return false;
    }

    /**
     * Queues the create a group form modal
     * @return bool
     */
    protected function queueUeCreateGroupForm()
    {
        self::$queue[] = [self::UE_CREATE_GROUP_FORM, [], 'override_key' => true];
        return true;
    }

    /**
     * Queues the create a role form modal
     * @return bool
     */
    protected function queueUeCreateRoleForm()
    {
        self::$queue[] = [self::UE_CREATE_ROLE_FORM, [], 'override_key' => true];
        return true;
    }

    /**
     * Queues the update a group form modal
     * @param null $groupCode
     * @return bool
     */
    protected function queueUeUpdateGroupForm($groupCode = null)
    {
        if($groupCode == null)
            $groupCode = $this->getCurrentGroup();
        if($groupCode === false)
            return false;
        $group = GroupManager::findGroup($groupCode);
        self::$queue[] = [self::UE_UPDATE_GROUP_FORM, ['group' => $group], 'override_key' => true];
        return true;
    }

    /**
     * Queues the update a role form modal
     * @param null $roleCode
     * @return bool
     */
    protected function queueUeUpdateRoleForm($roleCode = null)
    {
        if($roleCode == null)
            $roleCode = $this->getCurrentRole();
        if($roleCode === false)
            return false;
        $role = RoleManager::findRole($roleCode);
        self::$queue[] = [self::UE_UPDATE_ROLE_FORM, ['role' => $role], 'override_key' => true];
        return true;
    }

    /**
     * Queues the group list buttons
     * @return bool
     */
    protected function queueUeListGroupButtons()
    {
        $groups = GroupManager::allGroups()->getGroups();
        $currentGroupCode = $this->getCurrentGroup();
        if($currentGroupCode === false)
            return false;
        self::$queue[] = [self::UE_LIST_GROUP_BUTTONS, ['groups' => $groups, 'currentGroupCode' => $currentGroupCode]];
        return true;
    }

    /**
     * Queues the role list table
     * @return bool
     */
    protected function queueUeListRolesTable()
    {
        $page = $this->getCurrentPage('list_roles');
        if($page === false)
            $page = 1;
        $rolesUnsorted = RoleManager::with($this->getCurrentGroup());
        $roles = $rolesUnsorted->sort()->getRoles();//->paginate(self::RESULT_LIM, $page);
        $total = ceil((float)$roles->count() / (float)self::RESULT_LIM);
        $roles = $roles->slice(($page - 1)* self::RESULT_LIM, self::RESULT_LIM);
        if(!isset($roles))
            return false;
        self::$queue[] = [self::UE_LIST_ROLES_TABLE, ['roles' => $roles, 'pagination' => ['page' => $page, 'total' => $total]]];
        return true;
    }

    /**
     * Queues the unassigned-to-group roles table
     * @return bool
     */
    protected function queueUeListRolesTableUnassigned()
    {
        $roles = RoleManager::getUnassignedRoles();
        if(!isset($roles))
            return false;
        self::$queue[] = [self::UE_LIST_ROLES_TABLE_UNASSIGNED, ['roles' => $roles]];
        return true;
    }

    /**
     * Queues the create buttons toolbar
     * @return bool
     */
    protected function queueUeManageCreationToolbar()
    {
        self::$queue[] = [self::UE_MANAGE_CREATION_TOOLBAR, []];
        return true;
    }

    /**
     * Queues the group management toolbar
     * @return bool
     */
    protected function queueUeManageGroupToolbar()
    {
        $currentGroupCode = $this->getCurrentGroup();
        if($currentGroupCode === false)
            return false;
        $group = GroupManager::findGroup($currentGroupCode);
        self::$queue[] = [self::UE_MANAGE_GROUP_TOOLBAR, ['group' => $group]];
        return true;
    }

    /**
     * Queues the role management toolbar
     * @return bool
     */
    protected function queueUeManageRoleToolbar()
    {
        $currentRoleCode = $this->getCurrentRole();
        if($currentRoleCode === false)
            return false;
        $role = RoleManager::findRole($currentRoleCode);
        self::$queue[] = [self::UE_MANAGE_ROLE_TOOLBAR, ['role' => $role]];
        return true;
    }

    /**
     * Queues the overall management toolbar. This is the one which includes stats/analytics and delete groups.
     * @return bool
     */
    protected function queueUeManageOverallToolbar()
    {
        $currentGroupCode = $this->getCurrentGroup();
        if($currentGroupCode === false)
            return false;
        $group = GroupManager::findGroup($currentGroupCode);
        self::$queue[] = [self::UE_MANAGE_OVERALL_TOOLBAR, ['group' => $group]];
        return true;
    }

    /**
     * Queues the manage role UI
     * @return bool
     */
    protected function queueUeManageRoleUi()
    {
        $currentRoleCode = $this->getCurrentRole();
        if($currentRoleCode === false)
            return false;
        $role = RoleManager::findRole($currentRoleCode);
        self::$queue[] = [self::UE_MANAGE_ROLE_UI, ['role' => $role]];
        return true;
    }

    /**
     * Queues the manage users UI
     * @return bool
     */
    protected function queueUeManageUsersUi()
    {
        $currentRoleCode = $this->getCurrentRole();
        $role = RoleManager::findRole($currentRoleCode);

        $currentGroupCode = $this->getCurrentGroup();
        if($currentGroupCode === false)
            return false;

        $group = GroupManager::findGroup($currentGroupCode);

        $unassignedUsers = UsersGroups::byUsersWithoutRole($currentGroupCode)->get();

        self::$queue[] = [self::UE_MANAGE_USERS_UI, ['role' => $role, 'group' => $group, 'users' => $unassignedUsers]];
        return true;
    }

}