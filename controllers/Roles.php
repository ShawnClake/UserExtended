<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\GroupManager;
use Clake\UserExtended\Classes\RoleManager;
use Clake\UserExtended\Classes\UserGroupManager;
use Clake\UserExtended\Classes\UserRoleManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\UsersGroups;
use October\Rain\Support\Facades\Flash;
use Redirect;
use Backend;
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

    public static $queue = [];

    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $bodyClass = 'compact-container';

    //public $layout = 'roles';

    public function __construct()
    {
        parent::__construct();

        // Setting this context so that our sidebar menu works
        BackendMenu::setContext('RainLab.User', 'user', 'users');
		
		//Add CSS for some backend menus
		$this->addCss('/plugins/clake/userextended/assets/css/backend.css');
		$this->addJs('/plugins/clake/userextended/assets/js/backend.js');
    }

    /**
     * Action used for managing roles such as: their order, some stats, and their properties
    */
    public function manage()
    {
        $this->pageTitle = "Manage Roles";

        $this->vars['groups'] = GroupManager::allGroups()->getGroups();
        $this->vars['selectedGroup'] = GroupManager::allGroups()->getGroups()->first();
        $groupRoles = RoleManager::with($this->vars['selectedGroup']->code);
        $roleModels = $groupRoles->getSortedGroupRoles();

        $unassignedRoles = RoleManager::getUnassignedRoles();
        $this->vars['unassignedRoles'] = $unassignedRoles;
        $this->vars['unassignedRoleCount'] = $unassignedRoles->count();

        $unassignedUsers = UsersGroups::byUsersWithoutRole($this->vars['selectedGroup']->code)->get();
        $this->vars['unassignedUsers'] = $unassignedUsers;


        if(!isset($roleModels))
            return;
        $this->vars['groupRoles'] = ['roles' => $roleModels, 'roleCount' => $groupRoles->countRoles()];

        if(count($roleModels) > 0)
            $this->vars['role'] = reset($roleModels);
    }

    /**
     * AJAX handler used when a user clicks on a different group
     * @return array
     */
    public function onSelectGroup()
    {
        $groupCode = post('code');
        $this->vars['selectedGroup'] = GroupManager::findGroup($groupCode);
        $roles = RoleManager::with($groupCode)->sort()->getRoles();
        if($roles->count() > 0)
        {
            $roleRender = $this->renderRole($roles[0]->code, $groupCode);
            $roleToolbarRender = $this->renderManagementToolbar($roles[0]->code, $groupCode);
            $roleCode = $roles[0]->code;
        }
        else
        {
            $roleRender = ['#manage_role' => ''];
            $roleToolbarRender = ['#manage_role_toolbar' => $this->makePartial('management_role_toolbar', ['role' => null])];
            $roleCode = null;
        }

        return array_merge($this->renderRoles($groupCode),
            $this->renderToolbar($groupCode),
            $this->renderGroups($groupCode),
            $roleRender,
            $roleToolbarRender,
            $this->renderUnassignedRoles($groupCode),
            $this->renderUnassignedUsers($groupCode, $roleCode),
            $this->renderManageGroupToolbar($groupCode)
        );
    }

    /**
     * Renders the toolbar for managing a group
     * @param $groupCode
     * @return array
     */
    public function renderManageGroupToolbar($groupCode)
    {
        $group = GroupManager::findGroup($groupCode);

        return [
            '#manage_group_toolbar' => $this->makePartial('manage_group_toolbar', ['group' => $group]),
        ];
    }

    /**
     * Renders the table of users in a group without a role
     * @param $groupCode
     * @param $roleCode
     * @return array|void
     */
    public function renderUnassignedUsers($groupCode, $roleCode)
    {
        $group = GroupManager::findGroup($groupCode);
        $role = RoleManager::findRole($roleCode);
        $unassignedUsers = UsersGroups::byUsersWithoutRole($groupCode)->get();
        //echo json_encode($unassignedUsers);
        if(!isset($unassignedUsers))
            return;
        return [
          '#unassigned_users' => $this->makePartial('list_unassigned_user_in_group', ['users' => $unassignedUsers, 'group' => $group, 'role' => $role]),
        ];
    }

    /**
     * Returns the unassigned roles list
     * @return array|void
     */
    public function renderUnassignedRoles($groupCode)
    {
        $roles = RoleManager::getUnassignedRoles();
        $group = GroupManager::findGroup($groupCode);
        if(!isset($roles))
            return;
        return [
            '#unassigned_roles' => $this->makePartial('list_unassigned_roles', ['roles' => $roles, 'roleCount' => $roles->count(), 'group' => $group]),
        ];
    }

    /**
     * Renders the role list
     * @param $groupCode
     * @return array|void
     */
    public function renderRoles($groupCode)
    {
        $roles = RoleManager::with($groupCode);
        $roleModels = $roles->sort()->getRoles();
        if(!isset($roleModels))
            return;
        return [
            '#roles' => $this->makePartial('list_roles', ['roles' => $roleModels, 'roleCount' => $roles->countRoles()]),
        ];
    }

    /**
     * Renders the role management toolbar
     * @param $groupCode
     * @return array|void
     */
    public function renderToolbar($groupCode)
    {
        $group = GroupManager::findGroup($groupCode);
        if(!isset($group))
            return;
        return [
            '#management_toolbar' => $this->makePartial('management_toolbar', ['group' => $group]),
        ];
    }

    /**
     * Renders the group list w/ buttons
     * @param $groupCode
     * @return array|void
     */
    public function renderGroups($groupCode)
    {
        $groups = GroupManager::allGroups()->getGroups();
        $selectedGroup = GroupManager::findGroup($groupCode);
        if(!isset($groups))
            return;
        return [
            '#groups' => $this->makePartial('list_groups', ['groups' => $groups, 'selectedGroup' => $selectedGroup]),
        ];
    }

    /**
     * Renders the role management area to the screen
     * @param $roleCode
     * @param $groupCode
     * @return array|void
     */
    public function renderRole($roleCode, $groupCode)
    {

        $role = RoleManager::with($groupCode)->getRole($roleCode);

        if(!isset($role))
            return;

        return [
            '#manage_role' => $this->makePartial('manage_role', ['role' => $role]),
        ];
    }

    /**
     * Renders the role management area toolbar to the screen
     * @param $roleCode
     * @param $groupCode
     * @return array|void
     */
    public function renderManagementToolbar($roleCode, $groupCode)
    {
        $role = RoleManager::with($groupCode)->getRole($roleCode);

        if(!isset($role))
            return;

        return [
            '#manage_role_toolbar' => $this->makePartial('management_role_toolbar', ['role' => $role]),
        ];
    }

    /**
     * AJAX handler called when trying to move a role higher in the heirarchy
     * @return array|void
     */
    public function onMoveUp()
    {
        $groupCode = post('groupCode');
        $roleSortOrder = post('order');
        RoleManager::with($groupCode)->sortUp($roleSortOrder);
        return $this->renderRoles($groupCode);
    }

    /**
     * AJAX handler called when trying to move a role lower in the heirarchy
     * @return array|void
     */
    public function onMoveDown()
    {
        $groupCode = post('groupCode');
        $roleSortOrder = post('order');
        RoleManager::with($groupCode)->sortDown($roleSortOrder);
        return $this->renderRoles($groupCode);
    }

    /**
     * AJAX handler called when clicking on a different role to manage it
     * @return array
     */
    public function onManageRole()
    {
        $groupCode = post('groupCode');
        $roleCode = post('roleCode');
        return array_merge(
            $this->renderRole($roleCode, $groupCode),
            $this->renderManagementToolbar($roleCode, $groupCode),
            $this->renderUnassignedUsers($groupCode, $roleCode)
        );
    }

    /**
     * AJAX handler called when hitting the edit role button in the role manager.. Used to edit the role.
     * @return mixed
     */
    public function onOpenRole()
    {
        //$groupCode = post('groupCode');
        $roleCode = post('roleCode');
        $role = RoleManager::findRole($roleCode);
        return $this->makePartial('update_role_form', ['role' => $role]);
    }

    /**
     * AJAX handler called to save the role after the user clicks save in the role editor window
     * @return array
     */
    public function onSaveRole()
    {
        $groupCode = post('groupCode');
        $roleCode = post('roleCode');
        $name = post('name');
        $code = post('code');
        $description = post('description');

        $feedback = RoleManager::updateRole($roleCode, null, $name, $description, $code, null, true);

        $roles = RoleManager::with($groupCode)->sort()->getRoles();
        if($roles->count() > 0)
        {
            $roleRender = $this->renderRole($roles[0]->code, $groupCode);
            $roleToolbarRender = $this->renderManagementToolbar($roles[0]->code, $groupCode);
        }
        else
        {
            $roleRender = ['#manage_role' => ''];
            $roleToolbarRender = ['#manage_role_toolbar' => $this->makePartial('management_role_toolbar', ['role' => null])];
        }

        if($feedback === false || $feedback->fails())
        {
            Flash::error('Role was not saved!');

            if($feedback === false)
                $uiFeedback = ['#feedback_role_save' => '<span class="text-danger">That code already exists.</span>'];
            else
            {
                $errorString = '<span class="text-danger">';
                $errors = json_decode($feedback->messages());
                foreach($errors as $error)
                {
                    $errorString .= implode(' ', $error) . ' ';
                }

                $errorString .= '</span>';

                $uiFeedback = ['#feedback_role_save' => $errorString];
            }
        } else {
            Flash::success('Role successfully saved!');
            $uiFeedback = ['#feedback_role_save' => '<span class="text-success">Role has been saved.</span>'];
        }

        return array_merge($this->renderRoles($groupCode), $roleRender, $roleToolbarRender, $uiFeedback);

    }

    /**
     * AJAX handler for removing a role
     * @return array|void
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

            return $this->renderUnassignedRoles(post('selectedGroup'));

        }  else {

            $role = RoleManager::with($groupCode)->getRole($roleCode);

            if(!isset($role))
                return;

            $role->delete();

            $roles = RoleManager::with($groupCode)->sort()->getRoles();
            if($roles->count() > 0)
            {
                $roleRender = $this->renderRole($roles[0]->code, $groupCode);
                $roleToolbarRender = $this->renderManagementToolbar($roles[0]->code, $groupCode);
            }
            else
            {
                $roleRender = ['#manage_role' => ''];
                $roleToolbarRender = ['#manage_role_toolbar' => $this->makePartial('management_role_toolbar', ['role' => null])];
            }

            return array_merge($this->renderRoles($groupCode), $roleRender, $roleToolbarRender);

        }

    }

    /**
     * AJAX handler for opening a form for creating a new role
     * @return mixed
     */
    public function onOpenAddRole()
    {
        $groupCode = post('groupCode');
        $group = GroupManager::findGroup($groupCode);
        return $this->makePartial('create_role_form', ['group' => $group]);
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

        RoleManager::createRole($name, $description, $code, -1);

        return Redirect::to(Backend::url('clake/userextended/roles/manage'));
    }

    /**
     * Removes a user from a role. This does not remove them from the group.
     * It only sets their role_id to 0 in UsersGroups tbl
     * @return array|void
     */
    public function onRemoveUserFromRole()
    {
        $userId = post('userId');
        $roleId = post('roleId');

        $relation = UsersGroups::where('user_id', $userId)->where('role_id', $roleId)->first();
        $relation->role_id = 0;
        $relation->save();

        $role = \Clake\Userextended\Models\Role::where('id', $roleId)->first();

        return array_merge(
            $this->renderRole($role->code, $role->group->code),
            $this->renderUnassignedUsers($role->group->code, $role->code)
        );
    }

    /**
     * Promotes a user
     * @return array|void
     */
    public function onPromote()
    {
        $userId = post('userId');
        $roleCode = post('roleCode');

        $role = \Clake\Userextended\Models\Role::where('code', $roleCode)->first();

        UserRoleManager::with(UserUtil::getUser($userId))->allRoles()->promote($role->group->code);

        return $this->renderRole($role->code, $role->group->code);
    }

    /**
     * Demotes a user
     * @return array|void
     */
    public function onDemote()
    {
        $userId = post('userId');
        $roleCode = post('roleCode');

        $role = \Clake\Userextended\Models\Role::where('code', $roleCode)->first();

        UserRoleManager::with(UserUtil::getUser($userId))->allRoles()->demote($role->group->code);

        return $this->renderRole($role->code, $role->group->code);
    }

    /**
     * Handles assigning a role
     */
    public function onAssignRole()
    {
        $roleCode = post('roleCode');
        $groupCode = post('selectedGroup');

        $sortOrder = RoleManager::with($groupCode)->countRoles() + 1;
        $groupId = GroupManager::findGroup($groupCode)->id;

        RoleManager::updateRole($roleCode, $sortOrder, null, null, null, $groupId, true);

        $roles = RoleManager::with($groupCode)->sort()->getRoles();
        if($roles->count() > 0)
        {
            $roleRender = $this->renderRole($roles[0]->code, $groupCode);
            $roleToolbarRender = $this->renderManagementToolbar($roles[0]->code, $groupCode);
        }
        else
        {
            $roleRender = ['#manage_role' => ''];
            $roleToolbarRender = ['#manage_role_toolbar' => $this->makePartial('management_role_toolbar', ['role' => null])];
        }

        return array_merge($this->renderRoles($groupCode), $roleRender, $roleToolbarRender, $this->renderUnassignedRoles($groupCode));

    }

    /**
     * Handles unassigning a role
     * @return array
     */
    public function onUnassignRole()
    {
        $roleCode = post('roleCode');
        $groupCode = post('groupCode');

        /*
         * Removes all user from this role
         */
        $rows = UsersGroups::byRole($roleCode)->get();
        foreach($rows as $relation)
        {
            $relation->role_id = 0;
            $relation->save();
        }

        RoleManager::updateRole($roleCode, 1, null, null, null, 0, true);

        RoleManager::with($groupCode)->fixRoleSort();

        $roles = RoleManager::with($groupCode)->sort()->getRoles();
        if($roles->count() > 0)
        {
            $roleRender = $this->renderRole($roles[0]->code, $groupCode);
            $roleToolbarRender = $this->renderManagementToolbar($roles[0]->code, $groupCode);
        }
        else
        {
            $roleRender = ['#manage_role' => ''];
            $roleToolbarRender = ['#manage_role_toolbar' => $this->makePartial('management_role_toolbar', ['role' => null])];
        }

        return array_merge($this->renderRoles($groupCode), $roleRender, $roleToolbarRender, $this->renderUnassignedRoles($groupCode));
    }

    /**
     * AJAX handler for deleting a group
     * @return mixed
     */
    public function onDeleteGroup()
    {
        $groupCode = post('selectedGroup');

        GroupManager::deleteGroup($groupCode);

        return Redirect::to(Backend::url('clake/userextended/roles/manage'));
    }

    /**
     * AJAX handler for opening the create a group modal
     * @return mixed
     */
    public function onOpenCreateGroup()
    {
        return $this->makePartial('create_group_form');
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

        GroupManager::createGroup($name, $description, $code);

        return Redirect::to(Backend::url('clake/userextended/roles/manage'));
    }

    /**
     * AJAX handler to open the update group modal
     * @return mixed
     */
    public function onOpenGroup()
    {
        $groupCode = post('groupCode');
        $group = GroupManager::findGroup($groupCode);
        return $this->makePartial('update_group_form', ['group' => $group]);
    }

    /**
     * AJAX handler for saving the update group modal
     * @return mixed
     */
    public function onSaveGroup()
    {
        $groupCode = post('groupCode');
        $name = post('name');
        $code = post('code');
        $description = post('description');

        GroupManager::updateGroup($groupCode, $name, $description, $code);

        return Redirect::to(Backend::url('clake/userextended/roles/manage'));
    }

    /**
     * AJAX handler for assigning groups already in a group to a role
     * @return array
     */
    public function onAssignUser()
    {
        $groupCode = post('selectedGroup');
        $roleCode = post('roleCode');
        $userId = post('userId');

       UserRoleManager::with(UserUtil::getUser($userId))->addRole($roleCode);

        return array_merge(
            $this->renderUnassignedUsers($groupCode, $roleCode),
            $this->renderRole($roleCode, $groupCode)
        );
    }

    /**
     * AJAX handler for removing a group from a user
     * @return array
     */
    public function onRemoveUserFromGroup()
    {
        $groupCode = post('selectedGroup');
        $roleCode = post('roleCode');
        $userId = post('userId');

        UserGroupManager::with(UserUtil::getUser($userId))->removeGroup($groupCode);

        return array_merge(
            $this->renderUnassignedUsers($groupCode, $roleCode)
        );
    }

    /**
     * AJAX handler for redirecting when clicking on a users name
     */
    public function onManageUser()
    {
        $userid = post('userId');
        if(!isset($userid))
            return;

        return Redirect::to(Backend::url('rainlab/user/users/preview/' . $userid));
    }

    /**
     * CODE BELOW THIS POINT IS FOR RELEASE 2.1.00 AND IS HERE IN PREPARATION
     * THIS IS NOT PRODUCTION CODE AND IS NOT USED OR REFERENCED ELSEWHERE IN THE CODE BASE
     * DO NOT USE THIS CODE
     */

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
            if(!$function())
                $success = false;
        }

        return $success;
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
            $partialName = reset($renderType);
            $divId = '#' . $partialName;
            $partial = $this->makePartial($partialName, next($renderType));

            if(isset($renderType['override_key']) && $renderType['override_key'] === true)
            {
                array_merge($renders, [$partial]);
            } else {
                array_merge($renders, [$divId => $partial]);
            }


            // '#manage_role' => $this->makePartial('manage_role', ['role' => $role])
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
        self::$queue[] = [self::UE_UPDATE_GROUP_FORM, ['groupCode' => $groupCode], 'override_key' => true];
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
        self::$queue[] = [self::UE_UPDATE_ROLE_FORM, ['roleCode' => $roleCode], 'override_key' => true];
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
        $rolesUnsorted = RoleManager::with($this->getCurrentGroup());
        $roles = $rolesUnsorted->sort()->getRoles();
        if(!isset($roles))
            return false;
        self::$queue[] = [self::UE_LIST_ROLES_TABLE, ['roles' => $roles]];
        return true;
    }

    /**
     * Queues the unassigned-to-group roles table
     * @return bool
     */
    protected function queueUeListRolesTableUnassigned()
    {
        $roles = RoleManager::getUnassignedRoles();
        $currentGroupCode = $this->getCurrentGroup();
        if($currentGroupCode === false)
            return false;
        if(!isset($roles))
            return false;
        self::$queue[] = [self::UE_LIST_ROLES_TABLE_UNASSIGNED, ['roles' => $roles, 'currentGroupCode' => $currentGroupCode]];
        return true;
    }

    /**
     * Queues the create buttons toolbar
     * @return bool
     */
    protected function queueUeManageCreationToolbar()
    {
        $currentGroupCode = $this->getCurrentGroup();
        if($currentGroupCode === false)
            return false;
        self::$queue[] = [self::UE_MANAGE_CREATION_TOOLBAR, ['currentGroupCode' => $currentGroupCode]];
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
        if($currentRoleCode === false)
            return false;
        $role = RoleManager::findRole($currentRoleCode);

        $currentGroupCode = $this->getCurrentGroup();
        if($currentGroupCode === false)
            return false;
        $group = GroupManager::findGroup($currentGroupCode);

        $unassignedUsers = UsersGroups::byUsersWithoutRole($currentGroupCode)->get();
        if(!isset($unassignedUsers))
            return false;

        self::$queue[] = [self::UE_MANAGE_USERS_UI, ['role' => $role, 'group' => $group, 'users' => $unassignedUsers]];
        return true;
    }

    /*
     * These are here for now so I don't have to scroll as much
     *
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
    */
}