<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\GroupManager;
use Clake\UserExtended\Classes\RoleManager;
use Clake\UserExtended\Classes\UserRoleManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\UsersGroups;
use October\Rain\Support\Facades\Flash;
use Redirect;
use Backend;

/**
 * TODO: Add better error checking
 * TODO: Add better documentation and follow conventions
 * TODO: Improve code and its readability.
 * TODO: Create an array for all of the partials that can be updated and have a dynamic way to refresh them
 */

/**
 * Roles Back-end Controller
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

        //BackendMenu::setContext('Clake.Userextended', 'userextended', 'roles');
        // Setting this context so that our sidebar menu works
        BackendMenu::setContext('RainLab.User', 'user', 'users');
    }

    /**
        * Action used for managing roles such as: their order, some stats, and their properties
    */
    public function manage()
    {
        $this->pageTitle = "Manage Roles";

        //dd(RoleManager::initGroupRolesByCode('developer')->get());
        $this->vars['groups'] = GroupManager::allGroups()->getGroups();
        $this->vars['selectedGroup'] = GroupManager::allGroups()->getGroups()->first();
        $groupRoles = RoleManager::for($this->vars['selectedGroup']->code);
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
            $this->vars['role'] = $roleModels[0];

        //$this->vars['selectedGroup'] = $this->selectedGroupd;
        //return $this->renderRoles($selected);
    }

    /**
     * AJAX handler used when a user clicks on a different group
     * @return array
     */
    public function onSelectGroup()
    {
        $groupCode = post('code');
        $this->vars['selectedGroup'] = GroupManager::findGroup($groupCode);
        $roles = RoleManager::for($groupCode)->sort()->getRoles();
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

        return array_merge($this->renderRoles($groupCode),
            $this->renderToolbar($groupCode),
            $this->renderGroups($groupCode),
            $roleRender,
            $roleToolbarRender,
            $this->renderUnassignedRoles($groupCode),
            $this->renderUnassignedUsers($groupCode),
            $this->renderManageGroupToolbar($groupCode)
        );
    }

    public function renderManageGroupToolbar($groupCode)
    {
        $group = GroupManager::findGroup($groupCode);

        return [
            '#manage_group_toolbar' => $this->makePartial('manage_group_toolbar', ['group' => $group]),
        ];
    }

    public function renderUnassignedUsers($groupCode)
    {
        $group = GroupManager::findGroup($groupCode);
        $unassignedUsers = UsersGroups::byUsersWithoutRole($groupCode)->get();
        //echo json_encode($unassignedUsers);
        if(!isset($unassignedUsers))
            return;
        return [
          '#unassigned_users' => $this->makePartial('list_unassigned_user_in_group', ['users' => $unassignedUsers, 'group' => $group]),
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
        $roles = RoleManager::for($groupCode);
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

        $role = RoleManager::for($groupCode)->getRole($roleCode);

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
        $role = RoleManager::for($groupCode)->getRole($roleCode);

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
        RoleManager::for($groupCode)->sortUp($roleSortOrder);
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
        RoleManager::for($groupCode)->sortDown($roleSortOrder);
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
        return array_merge($this->renderRole($roleCode, $groupCode), $this->renderManagementToolbar($roleCode, $groupCode));
    }

    /**
     * AJAX handler called when hitting the edit role button in the role manager.. Used to edit the role.
     * @return mixed
     */
    public function onOpenRole()
    {
        $groupCode = post('groupCode');
        $roleCode = post('roleCode');
        $role = RoleManager::for($groupCode)->getRole($roleCode);
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

        $role = RoleManager::for($groupCode)->getRole($roleCode);
        $role->name = $name;
        $role->code = $code;
        $role->description = $description;
        $role->save();

        $roles = RoleManager::for($groupCode)->sort()->getRoles();
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

            $role = RoleManager::for($groupCode)->getRole($roleCode);

            if(!isset($role))
                return;

            $role->delete();

            $roles = RoleManager::for($groupCode)->sort()->getRoles();
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
        $groupCode = post('groupCode');
        $name = post('name');
        $code = post('code');
        $description = post('description');

        $groupId = GroupManager::findGroup($groupCode)->id;

        $role = new \Clake\Userextended\Models\Roles();
        $role->group_id = $groupId;
        $role->name = $name;
        $role->code = $code;
        $role->description = $description;
        $role->save();

        $roles = RoleManager::for($groupCode)->sort()->getRoles();
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

        Flash::success('Role successfully created!');

        return array_merge($this->renderRoles($groupCode), $roleRender, $roleToolbarRender, ['#feedback_role_save' => '<span class="text-success">Role has been created.</span>']);
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

        $role = \Clake\Userextended\Models\Roles::where('id', $roleId)->first();

        return $this->renderRole($role->code, $role->group->code);
    }

    /**
     * Promotes a user
     * @return array|void
     */
    public function onPromote()
    {
        $userId = post('userId');
        $roleCode = post('roleCode');

        $role = \Clake\Userextended\Models\Roles::where('code', $roleCode)->first();

        UserRoleManager::for(UserUtil::getUser($userId))->allRoles()->promote($role->group->code);

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

        $role = \Clake\Userextended\Models\Roles::where('code', $roleCode)->first();

        UserRoleManager::for(UserUtil::getUser($userId))->allRoles()->demote($role->group->code);

        return $this->renderRole($role->code, $role->group->code);
    }

    /**
     * Handles assigning a role
     */
    public function onAssignRole()
    {
        $roleCode = post('roleCode');
        $groupCode = post('selectedGroup');

        $sortOrder = RoleManager::for($groupCode)->countRoles() + 1;
        $groupId = GroupManager::findGroup($groupCode)->id;

        RoleManager::updateRole($roleCode, $sortOrder, null, null, null, $groupId, true);

        $roles = RoleManager::for($groupCode)->sort()->getRoles();
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

        RoleManager::for($groupCode)->fixRoleSort();

        $roles = RoleManager::for($groupCode)->sort()->getRoles();
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

    public function onDeleteGroup()
    {
        $groupCode = post('selectedGroup');

        GroupManager::deleteGroup($groupCode);

        return Redirect::to(Backend::url('clake/userextended/roles/manage'));
    }

    public function onOpenCreateGroup()
    {
        return $this->makePartial('create_group_form');
    }

    public function onCreateGroup()
    {
        $name = post('name');
        $code = post('code');
        $description = post('description');

        GroupManager::createGroup($name, $description, $code);

        return Redirect::to(Backend::url('clake/userextended/roles/manage'));
    }

    public function onOpenGroup()
    {
        $groupCode = post('groupCode');
        $group = GroupManager::findGroup($groupCode);
        return $this->makePartial('update_group_form', ['group' => $group]);
    }

    public function onSaveGroup()
    {
        $groupCode = post('groupCode');
        $name = post('name');
        $code = post('code');
        $description = post('description');

        GroupManager::updateGroup($groupCode, $name, $description, $code);

        return Redirect::to(Backend::url('clake/userextended/roles/manage'));
    }

}