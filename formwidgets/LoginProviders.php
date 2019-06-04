<?php

namespace Clake\UserExtended\FormWidgets;

use Backend\Classes\FormWidgetBase;

class LoginProviders extends FormWidgetBase {

    public function widgetDetails() {
        return [
            'name' => 'Login Providers',
            'description' => 'Displays a list of login providers associated with a user.'
        ];
    }

    public function render() {
        $this->prepareVars();
        return $this->makePartial('loginproviders');
    }

    public function prepareVars() {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->model->{$this->valueFrom};
    }

}
