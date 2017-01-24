<?php


namespace Clake\UserExtended\Classes;

/**
 * Class UserExtended
 *
 * UserExtended Modular Control
 *
 * Extend this class to build a module for UserExtended.
 *
 * Usage example: Module Name: clakeForum  Function Call: getUserActivity()
 *      Then the call would look like: UserExtended::clakeForum()->getUserActivity();
 *
 * In order to add your module to the module registry you must create an instance of your extensible class
 * on run inside of your plugin's plugin.php
 *
 * @package Clake\UserExtended\Classes
 */
abstract class UserExtended
{

    /**
     * The name of the module
     * Please use the syntax of authorModuleName
     * @var null
     */
    public $name = null;

    /**
     * The full name of the author of the plugin
     * For example: Shawn Clake
     * @var null
     */
    public $author = null;

    /**
     * A brief description of the module's purpose
     * In the case of the forum module the description might be something like:
     *      "Provides user stats and forum content for other plugins to easily utilize"
     * @var null
     */
    public $description = null;

    /**
     * Whether or not other modules are able to access your modules extensible class.
     * Typically this should always be true, the only cases where you would want to override this would be
     * if your module doesn't provide any extra functions for other modules to use.
     * @var bool
     */
    public $visible = true;

    /**
     * The module registry
     * An array of all the registerd modules. This is populated at runtime.
     * @var array
     */
    private static $modules = [];

    /**
     * Component injection registry
     * @var array
     */
    private static $components = [];

    /**
     * Navigation injection registry
     * @var array
     */
    private static $navigation = [];

    /**
     * Lang injection registry
     * @var array
     */
    private static $lang = [];

    /**
     * UserExtended constructor.
     */
    public function __construct()
    {
        $this->register();

        self::$components[] = $this->injectComponents();

        self::$navigation[] = $this->injectNavigation();

        self::$lang[] = $this->injectLang();
    }

    /**
     * Creates a module record for the module registry.
     * The module registry has the format:
     *      [moduleName=>moduleRecord]
     */
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

    /**
     * Utilize a registered module
     * The call might look like: UserExtended::clakeForum()->getUserActivity();
     * @param $name
     * @param $args
     * @return bool
     */
    public static function __callStatic($name, $args)
    {
        if(!array_key_exists($name, self::$modules))
            return false;

        $module = self::$modules[$name];

        if(!$module->visible)

        return $module->instance;
    }

    /**
     * Override with an array to inject components into UserExtended
     * @return mixed
     */
    public abstract function injectComponents();

    /**
     * Override with an array to inject navigation into UserExtended
     * @return mixed
     */
    public abstract function injectNavigation();

    /**
     * Override with a lang array to inject lang into UserExtended
     * @return mixed
     */
    public abstract function injectLang();

}