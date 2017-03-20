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
		$name = post('name');
		$code = post('code');
		$description = post('description');
		$type = post('type');
		$validation = post('validation');
		$flags = post('flags');	//this is an array
		$data = post('data');
		
		$flagArray = ['enabled' => false,
					  'encryptable' => false,
					  'registerable' => false,
					  'editable' => false];
		if($flags != null){
			foreach ($flags as $option){
				foreach($flagArray as $key => $value){
					if($option == $key){
						$flagArray[$key] = true;
					}
				}
			}
		}
		
		FieldManager::createField($name, $code, $description, $type, $validation, $flagArray, $data);
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
		$name = post('name');
		$code = post('code');
		$description = post('description');
		$type = post('type');
		$validation = post('validation');
		$flags = post('flags');	//this is an array
		$data = post('data');
		
		$flagArray = ['enabled' => false,
					  'encryptable' => false,
					  'registerable' => false,
					  'editable' => false];
		if($flags != null){
			foreach ($flags as $option){
				foreach($flagArray as $key => $value){
					if($option == $key){
						$flagArray[$key] = true;
					}
				}
			}
		}
		
		FieldManager::updateField($name, $code, $description, $type, $validation, $flagArray, $data);
		return Redirect::to(Backend::url('clake/userextended/Settings/manage'));
		
	}
	
	public function onConfirmDelete(){

	    // This isn't needed. A confirmation can easily be brought up via October's data attributes API:
        // http://octobercms.com/docs/ajax/attributes-api
        // data-request-confirm

		//$name = post('name');
		//$selection = Db::table($this->table)->where('name', $name)->get();
		//return $this->makePartial('confirm_delete', ['selection' => $selection[0]]);
	}
	
	public function onDeleteField(){
		//TODO
		//$name = post('name');
		//DB::table($this->table)->where('name', $name)->delete();
		//return Redirect::to(Backend::url('clake/userextended/Settings/start'));
	}
}