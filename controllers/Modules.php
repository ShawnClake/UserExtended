<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\ModuleManager;
use Clake\UserExtended\Classes\UserExtended;
use Clake\Userextended\Models\Settings;
use October\Rain\Support\Facades\Markdown;
use Redirect;
use Session;
use Schema;
use Db;
use Backend;

/**
 * User Extended by Shawn Clake
 * Class Modules
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Controllers
 */
class Modules extends Controller
{

    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        // Setting this context so that our sidebar menu works
        BackendMenu::setContext('RainLab.User', 'user', 'modules');

        //Add CSS for some backend menus
        $this->addCss('/plugins/clake/userextended/assets/css/backend.css');
        $this->addJs('/plugins/clake/userextended/assets/js/general.js');
        $this->addJs('/plugins/clake/userextended/assets/js/backend.js');
    }

    /**
     * Index page
     */
    public function index()
    {
        $this->pageTitle = "Manage Modules";
        $modules = ModuleManager::all()->getModules();
        foreach($modules as $module)
        {
            $injectionStr = '';
            $flags = $module->flags;
            foreach($flags as $flag)
            {
                $name = substr($flag, 6);
                $injectionStr .= $name . ', ';
            }
            $module->injectionStr = $injectionStr;
        }

        $this->vars['modules'] = $modules;
        $this->vars['devMode'] = Settings::get('dev_mode', false);
    }

    /**
     * AJAX Handler for refreshing the list of modules
     * @return mixed
     */
    public function onRefreshModules()
    {
        ModuleManager::all()->refresh();
        return Redirect::to(Backend::url('clake/userextended/modules'));
    }

    /**
     * AJAX handler for viewing a modules documentation
     * @return mixed
     */
    public function onViewDocumentation()
    {
        $name = post('name');
        $documentation = UserExtended::$name()->getDocumentation();
        foreach($documentation as $page=>$content)
        {
            if(is_array($content) && key_exists('md', $content) && $content['md']) {
                $documentation[$page] = Markdown::parse($content[0]);
            } else if(is_array($content)) {
                $documentation[$page] = $content[0];
            }
        }
        return $this->makePartial('view_documentation', ['content' => $documentation]);
    }

    /**
     * AJAX handler for viewing a modules version history
     * @return mixed
     */
    public function onViewUpdateNotes()
    {
        $name = post('name');
        $updateNotes = UserExtended::$name()->getUpdateNotes();

        $module = ModuleManager::findModule($name);
        $module->updated = 0;
        $module->save();

        foreach($updateNotes as $version=>$content)
        {
            if(is_array($content) && key_exists('md', $content) && $content['md']) {
                $updateNotes[$version] = Markdown::parse($content[0]);
            } else if(is_array($content)) {
                $updateNotes[$version] = $content[0];
            }
        }

        return $this->makePartial('view_update_notes', ['content' => $updateNotes]);
    }

}