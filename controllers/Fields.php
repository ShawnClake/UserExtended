<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\Helpers;
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
 * Class Fields
 * User Extended is licensed under the MIT license.
 *
 * TODO: Cleanup this class
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\Userextended\Controllers
 */
class Fields extends Controller
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

        BackendMenu::setContext('RainLab.User', 'user', 'users');

        //Add CSS for some backend menus
        $this->addCss('/plugins/clake/userextended/assets/css/backend.css');
        $this->addJs('/plugins/clake/userextended/assets/js/general.js');
	$this->addJs('/plugins/clake/userextended/assets/js/backend.js');
    }

    public function manage()
    {
        $this->pageTitle = "Manage Fields";
        $this->vars['fields'] = UserSettingsManager::currentUser()->getSettingsTemplate();
    }

    public function getSettings()
    {
        return UserSettingsManager::getSetting();
    }

    public function onCreateField()
    {
        //TODO validate input
        $post = post();
        //var_dump($post);
        $flags = FieldManager::makeFlags(
            in_array('enabled', $post['flags']),
            in_array('registerable', $post['flags']),
            in_array('editable', $post['flags']),
            in_array('encrypt', $post['flags'])
        );

        $validation = $this->makeValidationArray($post);

        $data = $this->makeDataArray($post);

        $feedback = FieldManager::createField(
            $post['name'],
            $post['code'],
            $post['description'],
            $validation,
            $post['type'],
            $flags,
            $data
        );

		// TODO: $feedback is a queryBuilder type, its not of type Validator. The type should be changed in 2.2.00
        // TODO: See FieldManager::createField() for more details
		/*$uiFeedback = $this->feedbackGenerator($feedback, '#feedback_field_save', [
            'success' => 'Field saved successfully!',
            'error'   => 'Field was not saved!',
            'false'   => 'Name, code and description are required!'
        ], [
            'success' => '<span class="text-success">Field has been saved.</span>',
            'false'   => '<span class="text-danger">Name, code and description are required!</span>',
            'error'   => ''
        ]);*/

		// TODO: The FieldManager doesn't utilize the render system that I built into the RoleManager
        // This cannot be used like this
		//return array_merge($this->render(), $uiFeedback);
        return Redirect::to(Backend::url('clake/userextended/fields/manage'));
    }

    public function onAddField()
    {
        return $this->makePartial('create_field_form');
    }

    public function onEditField()
    {
        $name = post('code');
        $selection = FieldManager::findField($name);
        return $this->makePartial('update_field_form', ['selection' => $selection]);
    }

    public function onUpdateField()
    {
        //TODO Validate input

        $post = post();

        $flags = FieldManager::makeFlags(
            in_array('enabled', $post['flags']),
            in_array('registerable', $post['flags']),
            in_array('editable', $post['flags']),
            in_array('encrypt', $post['flags'])
        );

        $validation = $this->makeValidationArray($post);

        $data = $this->makeDataArray($post);

        FieldManager::updateField(
            $post['name'],
            $post['code'],
            $post['description'],
            $post['type'],
            $validation,
            $flags,
            $data
        );

        return Redirect::to(Backend::url('clake/userextended/fields/manage'));
    }

    public function onDeleteField()
    {
        $code = post('code');
        FieldManager::deleteField($code);
        return Redirect::to(Backend::url('clake/userextended/fields/manage'));
    }

    protected function makeValidationArray($post)
    {
        $validation['additional'] = Helpers::arrayKeyToVal($post, 'validation');
        $validation['content']    = Helpers::arrayKeyToVal($post, 'validation_content');
        $validation['regex']      = Helpers::arrayKeyToVal($post, 'validation_regex');
        $validation['min']        = Helpers::arrayKeyToVal($post, 'validation_min');
        $validation['max']        = Helpers::arrayKeyToVal($post, 'validation_max');

        if (isset($post['validation_flags'])) {
            foreach ($post['validation_flags'] as $vFlag) {
                $validation['flags'][] = $vFlag;
            }
        }

        return $validation;
    }

    protected function makeDataArray($post)
    {
        $data['placeholder'] = Helpers::arrayKeyToVal($post, 'data_placeholder');
        $data['class']       = Helpers::arrayKeyToVal($post, 'data_class');

        return $data;
    }

}
