<?php namespace Clake\UserExtended\Classes;

/**
 * User Extended by Shawn Clake
 * Class UserExtended
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
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
 * @method static UserExtended register()
 * @package Clake\UserExtended\Classes
 */
abstract class UserExtended extends Module
{

    /**
     * The module registry
     * An array of all the registered modules. This is populated at runtime.
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
     * Asset injection registry
     * @var array
     */
    private static $assets = [];

    /**
     * Bonds injection registry
     * These are for injecting more relation states between users
     * @var array
     */
    private static $bonds = [];

    /**
     * Stores an array of settings from the backend module manager page.
     * These determine whether or not modules will be loaded, and enable/disable injections
     * @var array
     */
    private static $settings = [];

    /**
     * Called after all the modules are loaded.
     * @return mixed
     */
    public abstract function initialize();

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

    /**
     * Override with an assets array to inject assets into UserExtended
     * @return mixed
     */
    public abstract function injectAssets();

    /**
     * Override with a bonds array to inject bonds into UserExtended
     * @return mixed
     */
    public abstract function injectBonds();

    /**
     * Returns the injected components
     * @return array
     */
    public static function getComponents()
    {
        return self::$components;
    }

    /**
     * Returns the injected navigation
     * @return array
     */
    public static function getNavigation()
    {
        return self::$navigation;
    }

    /**
     * Returns the injected lang
     * @return array
     */
    public static function getLang()
    {
        return self::$lang;
    }

    /**
     * Returns the injected settings
     * @return array
     */
    public static function getSettings()
    {
        return self::$settings;
    }

    /**
     * Returns the injected assets
     * @return mixed
     */
    public static function getAssets()
    {
        return self::$assets;
    }

    /**
     * Returns the injected bonds
     * @return array
     */
    public static function getBonds()
    {
        return self::$bonds;
    }

    /**
     * Allows us to use a factory pattern for registering modules. IE. syntax becomes ModuleClass::register(); instead of $module = new ModuleClass();
     */
    public function registerFactory() {}

    /**
     * UserExtended constructor.
     * This will return false if the child class doesn't have the required class properties
     * This will then register the module and inject what the modules specifies to inject.
     */
    public function __construct()
    {
        if(empty(self::$settings))
            $this->setup();

        if(empty($this->name) || empty($this->author) || empty($this->description) || empty($this->version))
            return false;

        if(!in_array($this->name, self::$settings['enabled']) && false) // Remove && false tag when we are ready to use settings.
            return false;

        $this->registerModule();

        $this->inject();
    }

    /**
     * The boot function should be called once from the UserExtended plugin.
     * Don't override this function and don't use it otherwise you may break
     * the modular load.
     */
    public static function boot()
    {
        self::fixDuplicates();

        self::initializeModules();
    }

    /**
     * Sets up the class via getting settings
     */
    private function setup()
    {
        // TODO: DB Query goes here once we have the backend module manager page setup. Query for settings.

        //$settings = ModuleSettings::all();
        $modules = [
            ['id'=>1,'name'=>'shawnPickler','version'=>'0.0.1','enabled'=>true,'inject_components'=>true],
            ['id'=>2,'name'=>'Cheese','version'=>'0.1.2','enabled'=>true,'inject_components'=>true]
        ];

        /*$settings = [];
        foreach($modules as $module)
        {
            if($module->enabled)
                $settings['enabled'][] = $module->name;
        }

        self::$settings = $settings;
        die(self::$settings);*/

        $settings = [];
        foreach($modules as $module)
        {
            if($module['enabled'])
                $settings['enabled'][] = $module['name'];
            if($module['inject_components'])
                $settings['inject']['components'][] = $module['inject_components'];
        }

        self::$settings = $settings;
        //die(json_encode(self::$settings));
    }

    /**
     * Creates a module record for the module registry.
     * The module registry has the format:
     *      [moduleName=>moduleRecord]
     */
    private function registerModule()
    {
        $module = new Module();
        $module->name = $this->name;
        $module->author = $this->author;
        $module->description = $this->description;
        $module->version = $this->version;
        $module->visible = $this->visible;
        $module->instance = $this;
        self::$modules[$this->name] = $module;
    }

    /**
     * Preforms the module injection
     */
    private function inject()
    {
        self::$components = array_merge(self::$components, $this->injectComponents());

        self::$navigation = array_merge(self::$navigation, $this->injectNavigation());

        self::$lang = array_merge(self::$lang, $this->injectLang());

        self::$assets = array_merge(self::$assets, $this->injectAssets());

        self::$bonds = array_merge(self::$bonds, $this->injectBonds());
    }

    /**
     * Renames component codes in the case that several components are injected with the same component code.
     * This helps to avoid the 'duplicate component' error.
     * If 4 components have the same code ( userSettings ), the 4 components, in order of registration, will have the following codes:
     * 1) userSettings
     * 2) userSettingsClassName   where ClassName is the class name of the component
     * 3) userSettingsClassName1
     * 4) userSettingsClassName2
     * It will continue to append an increasing number for any further tie breakers.
     */
    private static function fixDuplicates()
    {
        if(empty(self::$components))
            return;

        $fixed = [];
        $dupeCount = 0;

        foreach(self::$components as $className => $componentCode)
        {
            $finalCode = $componentCode;
            if(in_array($componentCode, $fixed))
                $finalCode = $componentCode . basename($className);

            while(in_array($finalCode, $fixed))
            {
                $dupeCount++;
                $finalCode = $componentCode . basename($className) . $dupeCount;
            }

            $fixed[$className] = $finalCode;
        }

        self::$components = $fixed;
    }

    /**
     * Runs the initialize function on each module
     */
    private static function initializeModules()
    {
        foreach(self::getModules() as $module)
            $module->instance->initialize();
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
			return false;

        return $module->instance;
    }

    /**
     * Returns a loaded module by name
     * Useful for grabbing a specific module for debugging by looking at its values.
     * If you want to run operations on the module, use the static call method instead.
     * @param $moduleName
     * @return bool|mixed
     */
    public static function getModule($moduleName)
    {
        if(!array_key_exists($moduleName, self::$modules))
            return false;

        return self::$modules[$moduleName];
    }

    /**
     * Determines whether a module is loaded or not.
     * Useful for debugging
     * @param $moduleName
     * @return bool
     */
    public static function isModuleLoaded($moduleName)
    {
        return array_key_exists($moduleName, self::$modules);
    }

    /**
     * Returns the version of a loaded module
     * Useful for ensuring code doesn't break if modules get updated.
     * Also useful if you want to provide multiple versions of the same module.
     * @param $moduleName
     * @return bool
     */
    public static function getModuleVersion($moduleName)
    {
        if(!array_key_exists($moduleName, self::$modules))
            return false;

        $module = self::$modules[$moduleName];

        return $module->version;
    }

    /**
     * Returns all modules
     * @return array
     */
    public static function getModules()
    {
        return self::$modules;
    }

    /**
     * Dumps the contents of all the registered modules.
     * This is useful for debugging
     */
    public static function dumpModules()
    {
        var_dump(self::$modules);
    }

}

/**
 * Class Module
 * Provides a struct like object for use when loading and using modules
 * @package Clake\UserExtended\Classes
 */
class Module
{

    /**
     * The name of the module
     * Please use the syntax of authorModuleName
     * @var null
     */
    public $name = '';

    /**
     * The full name of the author of the plugin
     * For example: Shawn Clake
     * @var null
     */
    public $author = '';

    /**
     * A brief description of the module's purpose
     * In the case of the forum module the description might be something like:
     *      "Provides user stats and forum content for other plugins to easily utilize"
     * @var null
     */
    public $description = '';

    /**
     * The version the module is running on.
     * For example: 0.0.1
     * @var null
     */
    public $version = '';

    /**
     * Whether or not other modules are able to access your modules extensible class.
     * Typically this should always be true, the only cases where you would want to override this would be
     * if your module does not provide any extra functions for other modules to use.
     * @var bool
     */
    public $visible = true;

    /**
     * Returns the modules name
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the modules author
     * @return null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Returns the modules description
     * @return null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the modules version
     * @return null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Returns the modules visibility state
     * @return bool
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Returns an array of documentation for the module
     * Returns documentation in MD or html format back to display on the module manager.
     * The key is the page name in slug form and the value is the documentation content for that page.
     * @return array
     */
    public function getDocumentation()
    {
        return [
            'home' => 'This module has not provided any documentation',
        ];
    }

    /**
     * Returns an array of update notes for the module
     * Returns an array where the key is the version number and value at that key is the update notes for that version.
     * @return array
     */
    public function getUpdateNotes()
    {
        return [
            $this->version => 'This module has not provided any update notes',
        ];
    }

}