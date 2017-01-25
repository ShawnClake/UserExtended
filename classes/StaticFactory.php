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

	public static function factory()
	{
		$class=get_called_class();
		return new $class;
	}

    /**
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