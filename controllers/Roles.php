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
        $groupCode = post('groupCode');
        $roleCode = post('roleCode');
        $role = RoleManager::with($groupCode)->getRole($roleCode);
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

        $role = RoleManager::with($groupCode)->getRole($roleCode);
        $role->name = $name;
        $role->code = $code;
        $role->description = $description;
        $role->save();

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

        Flash::success('Role successfully saved!');

        return array_merge($this->renderRoles($groupCode), $roleRender, $roleToolbarRender, ['#feedback_role_save' => '<span class="text-success">Role has been saved.</span>']);

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

}