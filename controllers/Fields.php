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

        FieldManager::createField(
            $post['name'],
            $post['code'],
            $post['description'],
            $validation,
            $post['type'],
            $flags,
            $data
        );

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
        if (empty($post['validation']))
            $validation['validation_additional'] = '';
        else
            $validation['validation_additional'] = $post['validation'];

        if (!empty($post['validation_content']))
            $validation['validation_content'] = $post['validation_content'];
        else
            $validation['validation_content'] = '';

        if (!empty($post['validation_regex']))
            $validation['validation_regex'] = $post['validation_regex'];
        else
            $validation['validation_regex'] = '';

        if (!empty($post['validation_min']))
            $validation['validation_min'] = $post['validation_min'];
        else
            $validation['validation_min'] = '';

        if (!empty($post['validation_max']))
            $validation['validation_max'] = $post['validation_max'];
        else
            $validation['validation_max'] = '';

        if (isset($post['validation_flags'])) {
            foreach ($post['validation_flags'] as $vFlag) {
                $validation['validation_flags'][] = $vFlag;
            }
        }

        return $validation;
    }

    protected function makeDataArray($post)
    {
        if (!empty($post['data_placeholder']))
            $data['data_placeholder'] = $post['data_placeholder'];
        else
            $data['data_placeholder'] = '';

        if (!empty($post['data_classes']))
            $data['data_classes'] = $post['data_classes'];
        else
            $data['data_classes'] = '';

        return $data;
    }

}