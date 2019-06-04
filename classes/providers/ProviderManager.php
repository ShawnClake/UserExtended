<?php

namespace Clake\UserExtended\Classes\Providers;

use System\Classes\PluginManager;

class ProviderManager {

    use \October\Rain\Support\Traits\Singleton;

    /**
     * @var array An array of provider types.
     */
    protected $providers;

    /**
     * @var array Cache of report widget registration callbacks.
     */
    private $providerCallbacks = [];

    /**
     * @var array An array of report widgets.
     */
    protected $providerAliases;

    /**
     * Initialize this singleton.
     */
    protected function init() {
        $this->pluginManager = PluginManager::instance();
    }

    /**
     * Returns a list of registered form widgets.
     * @return array Array keys are class names.
     */
    public function listProviders() {
        if ($this->providers === null) {
            $this->providers = [];

            /*
             * Load module widgets
             */
            foreach ($this->providerCallbacks as $callback) {
                $callback($this);
            }

            /*
             * Load plugin menu item types
             */
            $plugins = $this->pluginManager->getPlugins();

            foreach ($plugins as $plugin) {
                // Plugins doesn't have a register_menu_item_types method
                if (!method_exists($plugin, 'register_clake_social_providers'))
                    continue;

                // Plugin didn't register any menu item types
                if (!is_array($types = $plugin->register_clake_social_providers()))
                    continue;

                foreach ($types as $className => $typeInfo)
                    $this->registerProvider($className, $typeInfo);
            }
        }

        return $this->providers;
    }

    /*
     * Registers a single form form widget.
     */

    public function registerProvider($className, $widgetInfo = null) {
        $widgetAlias = isset($widgetInfo['alias']) ? $widgetInfo['alias'] : null;
        if (!$widgetAlias)
            $widgetAlias = Str::getClassId($className);

        $this->providers[$className] = $widgetInfo;
        $this->providerAliases[$widgetAlias] = $className;
    }

    /**
     * Manually registers form widget for consideration.
     * Usage:
     * <pre>
     *   WidgetManager::registerProviders(function($manager){
     *       $manager->registerProvider('Backend\Providers\CodeEditor', 'codeeditor');
     *       $manager->registerProvider('Backend\Providers\RichEditor', 'richeditor');
     *   });
     * </pre>
     */
    public function registerProviders(callable $definitions) {
        $this->providerCallbacks[] = $definitions;
    }

    /**
     * Returns a class name from a form widget alias
     * Normalizes a class name or converts an alias to it's class name.
     * @return string The class name resolved, or null.
     */
    public function resolveProvider($name) {
        if ($this->providers === null)
            $this->listProviders();

        $aliases = $this->providerAliases;

        if (isset($aliases[$name]))
            return $aliases[$name];

        return null;
    }

}
