<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\UserSettingsManager;
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
class Settings extends Controller
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
        BackendMenu::setContext('October.System', 'system', 'settings');
		SettingsManager::setContext('clake.userextended', 'settings');
		
		//Add CSS for some backend menus
		$this->addCss('/plugins/clake/userextended/assets/css/backend.css');
		$this->addJs('/plugins/clake/userextended/assets/js/backend.js');
    }
	
	public function manage()
    {
		$this->pageTitle = "Manage Fields";
		$this->vars['settings'] = UserSettingsManager::init()->getSettingsTemplate();
	}

	/*$table = $this->table;
		Db::table($table)->insert(
		['name' => 'first_name',
		'required' => true,
		'min' => 2,
		'max' => 255,
		'validation' => 'letters']);
		
		Db::table($table)->insert(
		['name' => 'last_name',
		'required' => true,
		'min' => 2,
		'max' => 255,
		'validation' => 'letters']);
		
		Db::table($table)->insert(
		['name' => 'email',
		'required' => true,
		'min' => 2,
		'max' => 255,
		'validation' => 'email']);
		
		Db::table($table)->insert(
		['name' => 'password',
		'required' => true,
		'min' => 8,
		'max' => 255]);*/
	
	public function getSettings(){
		//return Db::table($this->table)->select('*')->get();
	}
	
	public function onCreateField(){
		/*$name = post('name') ;
		$required = post('required');
		$min = null !== post('min') ? post('min') : 0;
		$max = null !== post('max') ? post('max') : 255;
		$validation = post('validation');
		
		Db::table($this->table)->insert(
		['name' => $name,
		'required' => $required,
		'min' => $min,
		'max' => $max,
		'validation' => $validation]);*/
		
		return Redirect::to(Backend::url('clake/userextended/Settings/start'));
	}
	
	public function onAddField(){
	    // TODO: You haven't specified where this is drawn to
		return $this->makePartial('create_new_field');
	}
	
	public function onEditField(){
		//$name = post('name');
		//$selection = Db::table($this->table)->where('name', $name)->get();
		//return $this->makePartial('edit_field', ['selection' => $selection[0]]);
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