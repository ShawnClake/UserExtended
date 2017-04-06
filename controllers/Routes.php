<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\Helpers;
use Clake\UserExtended\Classes\RouteManager;
use Clake\UserExtended\Classes\UserSettingsManager;
use Clake\UserExtended\Classes\FieldManager;
use Clake\Userextended\Models\Route;
use System\Classes\SettingsManager;
use October\Rain\Support\Facades\Flash;
use Redirect;
use Backend;
use Session;
use Schema;
use Db;

/**
 * User Extended by Shawn Clake
 * Class Roues
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Controllers
 */
class Routes extends Controller
{

    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    //public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();

        // Setting this context so that our sidebar menu works
        //BackendMenu::setContext('October.System', 'system', 'settings');
        //SettingsManager::setContext('clake.userextended', 'settings');

        //BackendMenu::setContext('October.Cms', 'cms', 'routes');
        BackendMenu::setContext('RainLab.User', 'user', 'routes');

        //Add CSS for some backend menus
        $this->addCss('/plugins/clake/userextended/assets/css/backend.css');
        $this->addJs('/plugins/clake/userextended/assets/js/backend.js');
    }

    public function manage()
    {
        $this->pageTitle = "Manage Routes";
        $this->vars['fields'] = Route::all();
    }

}