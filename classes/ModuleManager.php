<?php namespace Clake\UserExtended\Classes;

use Carbon\Carbon;

/**
 * User Extended by Shawn Clake
 * Class ModuleManager
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @method static ModuleManager all() ModuleManager
 * @package Clake\UserExtended\Classes
 */
class ModuleManager extends StaticFactory
{

    private $modules;

    public static function findModule($name)
    {
        return \Clake\Userextended\Models\Module::where('name', $name)->first();
    }

    public function refresh()
    {
        // Start by soft deleting not found modules
        $foundModules = UserExtended::getModules();

        // Loops through existing modules and checks whether they are now gone or have been updated
        foreach($this->modules as $module)
        {
            if(!(key_exists($module->name, $foundModules))) {
                $module->delete();
            } else {
                $foundModule = $foundModules[$module->name];
                if($module->version != $foundModule->version)
                {
                    $module->version = $foundModule->version;
                    $module->updated = true;
                    if($module->locked)
                        $module->enabled = false;
                    $module->module_updated_at = Carbon::now();
                    $module->save();
                }
            }
            unset($foundModules[$module->name]);
        }

        // Loops through trashed modules to see if they've been readded, in that case, just restore the existing record
        $trashedModules = \Clake\Userextended\Models\Module::onlyTrashed()->get();
        foreach($trashedModules as $trashedModule)
        {
            if(key_exists($trashedModule->name, $foundModules))
            {
                $trashedModule->restore();
                $trashedModule->save();
                unset($foundModules[$trashedModule->name]);
            }
        }

        // Goes through the remaining found modules and creates entries for them
        foreach($foundModules as $foundModule)
        {
            $newModule = new  \Clake\Userextended\Models\Module();
            $newModule->name = $foundModule->name;
            $newModule->author = $foundModule->author;
            $newModule->description = $foundModule->description;
            $newModule->version = $foundModule->version;
            $newModule->visible = $foundModule->visible;
            $newModule->enabled = true;
            $newModule->locked = false;
            $newModule->updated = true;
            $newModule->flags = [
                'injectComponents' => true,
                'injectNavigation' => true,
                'injectLang' => true,
                'injectAssets' => true,
                'injectBonds' => true,
            ];
            $newModule->module_updated_at = Carbon::now();
            $newModule->save();
        }

        $this->modules = \Clake\Userextended\Models\Module::all();

        return $this;
    }

    public function allFactory()
    {
        $this->modules = \Clake\Userextended\Models\Module::all();
        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }

}