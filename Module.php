<?php

namespace Clake\UserExtended;

use Clake\UserExtended\Traits\StaticFactoryTrait;
use Clake\UserExtended\Classes\UserExtended;

class Module extends UserExtended
{
    use StaticFactoryTrait;

    public $name = "clakeUserExtended";

    public $author = "Shawn Clake";

    public $description = "The core module for UserExtended";

    public $version = "1.1.00";

    public function initialize() {}

    public function injectComponents()
    {
        return [
            'Clake\UserExtended\Components\Account' => 'account',
            'Clake\UserExtended\Components\Friends' => 'friends',
            'Clake\UserExtended\Components\User' => 'ueuser',
        ];
    }

    public function injectNavigation()
    {
        return [];
    }

    public function injectLang()
    {
        return [];
    }

}