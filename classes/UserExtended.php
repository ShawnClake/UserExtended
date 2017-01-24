<?php


namespace Clake\UserExtended\Classes;

/**
 * Class UserExtended
 *
 * UserExtended Modular Control
 *
 * Extend this class to build a module for UserExtended.
 *
 *
 * @package Clake\UserExtended\Classes
 */
abstract class UserExtended
{

    public $name = null;

    public $author = null;

    public $description = null;

    public $visible = true;

    public static $modules = [];

    public function __construct()
    {
        $this->register();
    }

    public function register()
    {
        $module = new \stdClass();
        $module->name = $this->name;
        $module->author = $this->author;
        $module->description = $this->description;
        $module->visible = $this->visible;
        $module->instance = $this;
        self::$modules[$this->name] = $module;
    }

    public static function __callStatic($name, $args)
    {
        if(!array_key_exists($name, self::$modules))
            return false;

        return self::$modules[$name]->instance;
    }


}