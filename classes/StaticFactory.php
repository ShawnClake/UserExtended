<?php

namespace Clake\UserExtended\Classes;

/**
 * Class StaticFactory
 *
 * Used as a method of simplifying syntax whilst safely creating objects.
 * 		An example would be for a Class called ClassD to have a funcion destroyFactory() and appendAndPrint()
 * 			ClassD::destroy('never say never ')->appendAndPrint('not like this');
 *
 * Any functions inside of ClassD which you desire to be factory compatabile must have a function name that ends in 'Factory'
 * However when utilizing the factory, don't write the word 'Factory' as you saw in the example above.
 *
 * @package Clake\UserExtended\Classes
 */
class StaticFactory
{
    use StaticFactoryTrait;
}

/**
 * Class StaticFactoryTrait
 * @package Clake\UserExtended\Classes
 */
trait StaticFactoryTrait
{

    /**
     * Helper function which can also be used to simply create an instance of a child class in cases where initialization
     * functions aren't needed.
     * Generally you will want to use the static magic method below.
     * @return mixed
     */
	public static function factory()
	{
		$class=get_called_class();
		return new $class;
	}

    /**
     * Main factory function which utilizes PHP's magic method to call a suffixed factory function on the child class.
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $object = self::factory();
        return call_user_func_array(array($object, $name.'Factory'), $arguments);
    }

}