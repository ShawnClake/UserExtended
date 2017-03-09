<?php namespace Clake\UserExtended\Console;

use October\Rain\Scaffold\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * User Extended by Shawn Clake
 * Class CreateUEModule
 * User Extended is licensed under the MIT license.
 *
 * This template code was taken from OctoberCMS.Library repo
 * It was modified by Shawn Clake.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Console
 */
class CreateUEModule extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'create:uemodule';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new ue module.';
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Module';
    /**
     * A mapping of stub to generated file.
     *
     * @var array
     */
    protected $stubs = [
        'module/module.stub'  => 'Module.php',
    ];
    /**
     * Prepare variables for stubs.
     *
     * return @array
     */
    protected function prepareVars()
    {
        /*
         * Extract the author and name from the plugin code
         */
        $pluginCode = $this->argument('module');
        $parts = explode('.', $pluginCode);
        if (count($parts) != 2) {
            $this->error('Invalid plugin name, either too many dots or not enough.');
            $this->error('Example name: AuthorName.PluginName');
            return;
        }
        $pluginName = array_pop($parts);
        $authorName = array_pop($parts);
        return [
            'name'   => $pluginName,
            'author' => $authorName,
        ];
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of the plugin to create a module for. Eg: RainLab.Blog'],
        ];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Overwrite existing files with generated ones.'],
        ];
    }
}