<?php

namespace Clake\UserExtended;

use Clake\UserExtended\Classes\StaticFactoryTrait;
use Clake\UserExtended\Classes\UserExtended;

class Module extends UserExtended
{
    use StaticFactoryTrait;

    public $name = "clakeUserExtended";

    public $author = "Shawn Clake";

    public $description = "The core module for UserExtended";

    public $version = "1.00.00";

    public function injectComponents()
    {
        return [];
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