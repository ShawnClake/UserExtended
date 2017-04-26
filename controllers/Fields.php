<?php namespace Clake\Userextended\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Clake\UserExtended\Classes\Feedback\FieldFeedback;
use Clake\UserExtended\Classes\Helpers;
use Clake\UserExtended\Classes\UserSettingsManager;
use Clake\UserExtended\Classes\FieldManager;
use Redirect;
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

    public function __construct()
    {
        parent::__construct();

        // Setting this context so that our sidebar menu works
        BackendMenu::setContext('RainLab.User', 'user', 'fields');

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

    /**
     * AJAX handler for creating a new field
     * @return mixed
     */
    public function onCreateField()
    {
        $post = post();
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

        FieldFeedback::with($feedback, true)->flash();

        return Redirect::to(Backend::url('clake/userextended/fields/manage'));
    }

    /**
     * AJAX handler for popping up the create field form
     * @return mixed
     */
    public function onAddField()
    {
        return $this->makePartial('create_field_form');
    }

    /**
     * AJAX handler for popping up the edit field form
     * @return mixed
     */
    public function onEditField()
    {
        $name = post('code');
        $selection = FieldManager::findField($name);
        return $this->makePartial('update_field_form', ['selection' => $selection]);
    }

    /**
     * AJAX handler for persisting field edits
     * @return mixed
     */
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

        $feedback = FieldManager::updateField(
            $post['name'],
            $post['code'],
            $post['description'],
            $post['type'],
            $validation,
            $flags,
            $data
        );

        FieldFeedback::with($feedback, true)->flash();

        return Redirect::to(Backend::url('clake/userextended/fields/manage'));
    }

    /**
     * AJAX handler for deleting a field
     * @return mixed
     */
    public function onDeleteField()
    {
        $code = post('code');
        FieldManager::deleteField($code);
        return Redirect::to(Backend::url('clake/userextended/fields/manage'));
    }

    /**
     * Creates a validation array. This is an intermediary step which makes it easier to persist
     * @param $post
     * @return mixed
     */
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

    /**
     * Creates the data array
     * @param $post
     * @return mixed
     */
    protected function makeDataArray($post)
    {
        $data['placeholder'] = Helpers::arrayKeyToVal($post, 'data_placeholder');
        $data['class']       = Helpers::arrayKeyToVal($post, 'data_class');

        return $data;
    }

}