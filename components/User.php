<?php namespace Clake\Userextended\Components;

use Cms\Classes\ComponentBase;

class User extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'User Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

}