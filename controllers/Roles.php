<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\GroupManager;
use Clake\UserExtended\Classes\RoleManager;
use October\Rain\Support\Facades\Flash;

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
        $this->vars['groups'] = GroupManager::all()->get();
        $this->vars['selectedGroup'] = GroupManager::all()->get()->first();

        $groupRoles = RoleManager::initGroupRolesByCode($this->vars['selectedGroup']->code);
        $roleModels = $groupRoles->getSorted();
        if(!isset($roleModels))
            return;
        $this->vars['groupRoles'] = ['roles' => $roleModels, 'roleCount' => $groupRoles->count()];

        if($roleModels->count() > 0)
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
        $this->vars['selectedGroup'] = GroupManager::retrieve($groupCode);
        $roles = RoleManager::initGroupRolesByCode($groupCode)->getSorted();
        if($roles->count() > 0)
        {
            $roleRender = $this->renderRole($roles[0]->code, $groupCode);
            $roleToolbarRender = $this->renderManagementToolbar($roles[0]->code, $groupCode);
        }
        else
        {
            $roleRender = ['#manage_role' => 'No roles currently exist in this group'];
            $roleToolbarRender = [];
        }

        return array_merge($this->renderRoles($groupCode), $this->renderToolbar($groupCode), $this->renderGroups($groupCode), $roleRender, $roleToolbarRender);
    }

    /**
     * Renders the role list
     * @param $groupCode
     * @return array|void
     */
    public function renderRoles($groupCode)
    {
        $roles = RoleManager::initGroupRolesByCode($groupCode);
        $roleModels = $roles->getSorted();
        if(!isset($roleModels))
            return;
        return [
            '#roles' => $this->makePartial('list_roles', ['roles' => $roleModels, 'roleCount' => $roles->count()]),
        ];
    }

    /**
     * Renders the role management toolbar
     * @param $groupCode
     * @return array|void
     */
    public function renderToolbar($groupCode)
    {
        $group = GroupManager::retrieve($groupCode);
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
        $groups = GroupManager::all()->get();
        $selectedGroup = GroupManager::retrieve($groupCode);
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

        $role = RoleManager::initGroupRolesByCode($groupCode)->getRoleIfExists($roleCode);

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
        $role = RoleManager::initGroupRolesByCode($groupCode)->getRoleIfExists($roleCode);

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
        RoleManager::initGroupRolesByCode($groupCode)->sortUp($roleSortOrder);
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
        RoleManager::initGroupRolesByCode($groupCode)->sortDown($roleSortOrder);
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
        $role = RoleManager::initGroupRolesByCode($groupCode)->getRoleIfExists($roleCode);
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

        $role = RoleManager::initGroupRolesByCode($groupCode)->getRoleIfExists($roleCode);
        $role->name = $name;
        $role->code = $code;
        $role->description = $description;
        $role->save();

        $roles = RoleManager::initGroupRolesByCode($groupCode)->getSorted();
        if($roles->count() > 0)
        {
            $roleRender = $this->renderRole($roles[0]->code, $groupCode);
            $roleToolbarRender = $this->renderManagementToolbar($roles[0]->code, $groupCode);
        }
        else
        {
            $roleRender = ['#manage_role' => 'No roles currently exist in this group'];
            $roleToolbarRender = [];
        }

        Flash::success('Role successfully saved!');

        return array_merge($this->renderRoles($groupCode), $roleRender, $roleToolbarRender, ['#feedback_role_save' => '<span class="text-success">Role has been saved.</span>']);

    }
}