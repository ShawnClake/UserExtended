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

        $feedback = FieldManager::createField(
            $post['name'],
            $post['code'],
            $post['description'],
            $validation,
            $post['type'],
            $flags,
            $data
        );
		
		$uiFeedback = $this->feedbackGenerator($feedback, '#feedback_field_save', [
            'success' => 'Field saved successfully!',
            'error'   => 'Field was not saved!',
            'false'   => 'Name, code and description are required!'
        ], [
            'success' => '<span class="text-success">Field has been saved.</span>',
            'false'   => '<span class="text-danger">Name, code and description are required!</span>',
            'error'   => ''
        ]);

		return array_merge($this->render(), $uiFeedback);
        //return Redirect::to(Backend::url('clake/userextended/fields/manage'));
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
	
	    protected function feedbackGenerator($validator, $destinationDiv = '#feedback',
                                         array $flash = ['success' => 'Success!', 'error' => 'Something went wrong.', 'false' => ''],
                                         array $message = ['success' => 'Success!', 'error' => 'Something went wrong.', 'false' => ''])
    {
        if($validator === false)
        {
            Flash::error($flash['false']);
            return [$destinationDiv => $message['false']];
        }

        if($validator->fails())
        {
            Flash::error($flash['error']);
            $errorString = $message['error'] . '<span class="text-danger">';
            $errors = json_decode($validator->messages());
            foreach($errors as $error)
            {
                $errorString .= implode(' ', $error) . ' ';
            }
            $errorString .= '</span>';
            return [$destinationDiv => $errorString];
        }

        Flash::success($flash['success']);
        return [$destinationDiv => $message['success']];
    }
	
	   /**
     * Renders each of the passed const's and returns a merged array of them
     * $to_render is an array containing arrays of the following format [UE_CREATE_ROLE_FORM, ['group' => groupCode, 'role' => roleCode]]
     * @param array $to_render
     * @return array
     */
    protected function render(array $to_render = [])
    {
        if(empty($to_render))
            $to_render = self::$queue;

        if(empty($to_render))
            return [];

        $renders = [];

        foreach($to_render as $renderType)
        {
            $partialName = $renderType[0];
            $divId = '#' . $partialName;
            $vars = $renderType[1];

            if(isset($renderType['blank']) && $renderType['blank'] === true)
            {
                $renders = array_merge($renders, [$divId => '']);
                continue;
            }

            $partial = $this->makePartial($partialName, $vars);

            if(isset($renderType['override_key']) && $renderType['override_key'] === true)
            {
                $renders = array_merge($renders, [$partial]);
            } else {
                $renders = array_merge($renders, [$divId => $partial]);
            }
        }

        $this->flushQueue();

        return $renders;
    }

    /**
     * Flushes the queue
     * Remove all renders in queue
     */
    private function flushQueue()
    {
        self::$queue = [];
    }

}