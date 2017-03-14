<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\GroupManager;
use Clake\UserExtended\Classes\RoleManager;
use System\Classes\SettingsManager;
use Clake\UserExtended\Classes\UserGroupManager;
use Clake\UserExtended\Classes\UserRoleManager;
use Clake\UserExtended\Classes\UserUtil;
use Clake\Userextended\Models\UsersGroups;
use October\Rain\Support\Facades\Flash;
use Redirect;
use Backend;
use Session;
use Schema;
use Db;

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
class Settings extends Controller
{
   public static $queue = [];

    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
	
	private $table = 'clake_userextended_settings';

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
	
	public function start(){
		$this->pageTitle = "Manage locations";
		$this->checkTable();
		//die("Hey! Listen!");
		//$this->makePartial('init');
	}
	
	public function checkTable(){
		if(Schema::hasTable($this->table)){
			return;
		} else {
			Schema::create($this->table, function ($table) {
				$table->increments('id');
				$table->text('name');
				$table->boolean('required');
				$table->integer('min');
				$table->integer('max');
				$table->text('validation');
				});
			$this->populateTable();
		}
	}
	
	public function populateTable(){
	$table = $this->table;
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
		'max' => 255]);
	}
	
	public function getSettings(){
		return Db::table($this->table)->select('*')->get();
	}
}