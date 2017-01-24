<?php


namespace Clake\UserExtended\Classes;

/**
 * Class StaticFactory
 * @package Clake\UserExtended\Classes
 */
class StaticFactory
{

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        $object = new static;
        return call_user_func_array(array($object, $name), $arguments);
    }


}