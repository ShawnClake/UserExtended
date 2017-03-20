<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\UserSettingsManager;
use Clake\UserExtended\Classes\FieldManager;
use System\Classes\SettingsManager;
use October\Rain\Support\Facades\Flash;
use Redirect;
use Backend;
use Session;
use Schema;
use Db;

/**
 * User Extended by Shawn Clake
 * Class Controller
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Controllers
 */
class Fields extends Controller
{	

   public static $queue = [];

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

        BackendMenu::setContext('RainLab.User', 'user', 'users');
		
		//Add CSS for some backend menus
		$this->addCss('/plugins/clake/userextended/assets/css/backend.css');
		$this->addJs('/plugins/clake/userextended/assets/js/backend.js');
    }
	
	public function manage()
    {
		$this->pageTitle = "Manage Fields";
		$this->vars['fields'] = UserSettingsManager::currentUser()->getSettingsTemplate();
	}
	
	public function getSettings(){
		return UserSettingsManager::getSetting();
	}
	
	public function onCreateField(){
		//TODO validate input
        $post = post();

        $flags = FieldManager::makeFlags(
            $post['flags']['enabled'],
            $post['flags']['registerable'],
            $post['flags']['editable'],
            $post['flags']['encrypt']
        );

        FieldManager::createField(
            $post['name'],
            $post['code'],
            $post['description'],
            $post['type'],
            $post['validation'],
            $flags,
            $post['data']
        );

		return Redirect::to(Backend::url('clake/userextended/Settings/manage'));
	}
	
	public function onAddField(){
		return $this->makePartial('create_field_form');
	}
	
	public function onEditField(){
		$name = post('code');
		$selection = FieldManager::findField($name);
		return $this->makePartial('update_field_form', ['selection' => $selection]);
	}
	
	public function onUpdateField(){
		//TODO Validate input

		$post = post();

		$flags = FieldManager::makeFlags(
		    $post['flags']['enabled'],
            $post['flags']['registerable'],
            $post['flags']['editable'],
            $post['flags']['encrypt']
        );
		
		FieldManager::updateField(
		    $post['name'],
            $post['code'],
            $post['description'],
            $post['type'],
            $post['validation'],
            $flags,
            $post['data']
        );

		return Redirect::to(Backend::url('clake/userextended/Settings/manage'));
		
	}
	
	public function onDeleteField(){
		$name = post('name');
		FieldManager::deleteField($name);
		return Redirect::to(Backend::url('clake/userextended/Settings/manage'));
	}
}